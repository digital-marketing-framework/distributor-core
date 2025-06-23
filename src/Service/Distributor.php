<?php

namespace DigitalMarketingFramework\Distributor\Core\Service;

use DigitalMarketingFramework\Core\Context\ContextAwareInterface;
use DigitalMarketingFramework\Core\Context\ContextAwareTrait;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\GlobalConfiguration\GlobalConfigurationAwareInterface;
use DigitalMarketingFramework\Core\GlobalConfiguration\GlobalConfigurationAwareTrait;
use DigitalMarketingFramework\Core\Log\LoggerAwareInterface;
use DigitalMarketingFramework\Core\Log\LoggerAwareTrait;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Queue\QueueException;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\RestrictedTermsSchema;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactoryInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Queue\GlobalConfiguration\Settings\QueueSettings;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRouteInterface;

class Distributor implements DistributorInterface, LoggerAwareInterface, ContextAwareInterface, GlobalConfigurationAwareInterface
{
    use LoggerAwareTrait;
    use ContextAwareTrait;
    use GlobalConfigurationAwareTrait;

    protected QueueInterface $persistentQueue;

    protected QueueInterface $temporaryQueue;

    protected QueueDataFactoryInterface $queueDataFactory;

    protected ?QueueSettings $queueSettings = null;

    public function __construct(protected RegistryInterface $registry)
    {
        $this->persistentQueue = $registry->getPersistentQueue();
        $this->temporaryQueue = $registry->getNonPersistentQueue();
        $this->queueDataFactory = $registry->getQueueDataFactory();
    }

    protected function addContext(SubmissionDataSetInterface $submission): void
    {
        $this->registry->addServiceContext($submission->getContext());

        $dataProviders = $this->registry->getDataProviders($submission);
        foreach ($dataProviders as $dataProvider) {
            try {
                $dataProvider->addContext($submission->getContext());
            } catch (DigitalMarketingFrameworkException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        $routes = $this->registry->getOutboundRoutes($submission);
        foreach ($routes as $route) {
            try {
                $route->addContext($submission->getContext());
            } catch (DigitalMarketingFrameworkException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @param array<string> $enabledDataProviders
     */
    protected function processDataProviders(SubmissionDataSetInterface $submission, array $enabledDataProviders = ['*'], bool $preview = false): void
    {
        $dataProviders = $this->registry->getDataProviders($submission);
        foreach ($dataProviders as $dataProvider) {
            $keyword = $dataProvider->getKeyword();
            if (RestrictedTermsSchema::isTermAllowed($enabledDataProviders, $keyword)) {
                if ($preview) {
                    $dataProvider->addDataForPreview();
                } else {
                    $dataProvider->addData();
                }
            }
        }
    }

    public function processJob(JobInterface $job): bool
    {
        $contextPushed = false;
        try {
            $submission = $this->queueDataFactory->convertJobToSubmission($job);
            $submission->getContext()->setResponsive(false);

            $this->registry->pushContext($submission->getContext());
            $contextPushed = true;

            $routeId = $this->queueDataFactory->getJobRouteId($job);
            $integrationName = $this->queueDataFactory->getJobRouteIntegrationName($job);
            $route = $this->registry->getOutboundRoute($submission, $integrationName, $routeId);
            if (!$route instanceof OutboundRouteInterface) {
                throw new DigitalMarketingFrameworkException(sprintf('route with ID "%s" not found in integration "%s"', $routeId, $integrationName));
            }

            $this->processDataProviders($submission, $route->getEnabledDataProviders());

            $result = $route->process();
            $this->registry->popContext();

            return $result;
        } catch (DigitalMarketingFrameworkException $e) {
            if ($contextPushed) {
                $this->registry->popContext();
            }

            $this->logger->error($e->getMessage());
            throw new QueueException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getPreviewData(JobInterface $job): array
    {
        $contextPushed = false;
        try {
            $submission = $this->queueDataFactory->convertJobToSubmission($job);
            $submission->getContext()->setResponsive(false);

            $this->registry->pushContext($submission->getContext());
            $contextPushed = true;

            $routeId = $this->queueDataFactory->getJobRouteId($job);
            $integrationName = $this->queueDataFactory->getJobRouteIntegrationName($job);
            $route = $this->registry->getOutboundRoute($submission, $integrationName, $routeId);
            if (!$route instanceof OutboundRouteInterface) {
                throw new DigitalMarketingFrameworkException(sprintf('Route with ID "%s" not found in integration "%s"', $routeId, $integrationName));
            }

            $this->processDataProviders($submission, $route->getEnabledDataProviders(), preview: true);

            $result = $route->preview();
            $this->registry->popContext();

            return $result;
        } catch (DigitalMarketingFrameworkException $e) {
            if ($contextPushed) {
                $this->registry->popContext();
            }

            return [
                'fatal' => $e->getMessage(),
            ];
        }
    }

    public function getQueueSettings(): QueueSettings
    {
        if (!$this->queueSettings instanceof QueueSettings) {
            $this->queueSettings = $this->globalConfiguration->getGlobalSettings(QueueSettings::class);
        }

        return $this->queueSettings;
    }

    public function process(SubmissionDataSetInterface $submission): array
    {
        $this->addContext($submission);

        $queueSettings = $this->getQueueSettings();

        $syncPersistentJobs = [];
        $syncTemporaryJobs = [];
        $allJobs = [];
        $routes = $this->registry->getOutboundRoutes($submission);
        $retryAmount = $queueSettings->rerunFailedJobEnabled() ? $queueSettings->getRerunFailedJobAmount() : 0;
        $host = $this->context->getHost() ?? '';

        foreach ($routes as $route) {
            if (!$route->enabled()) {
                continue;
            }

            $async = $route->async() ?? $submission->getConfiguration()->async();
            $enableStorage = $route->enableStorage() ?? $submission->getConfiguration()->enableStorage();

            if (!$enableStorage && $async) {
                $this->logger->error('Async submissions without storage are not possible. Using sync submission instead.');
                $async = false;
            }

            $status = $async ? QueueInterface::STATUS_QUEUED : QueueInterface::STATUS_PENDING;
            $queue = $enableStorage ? $this->persistentQueue : $this->temporaryQueue;

            $job = $this->queueDataFactory->convertSubmissionToJob(
                $submission,
                $route->getIntegrationInfo()->getName(),
                $route->getRouteId(),
                $status
            );
            $job->setEnvironment($host);
            $job->setRetryAmount($route->canRetryOnFail() ? $retryAmount : 0);
            $queue->add($job);
            $allJobs[] = $job;
            if (!$async) {
                if ($enableStorage) {
                    $syncPersistentJobs[] = $job;
                } else {
                    $syncTemporaryJobs[] = $job;
                }
            }
        }

        if ($syncTemporaryJobs !== []) {
            $processor = $this->registry->getQueueProcessor($this->temporaryQueue, $this);
            $processor->processJobs($syncTemporaryJobs);
        }

        if ($syncPersistentJobs !== []) {
            $processor = $this->registry->getQueueProcessor($this->persistentQueue, $this);
            $processor->processJobs($syncPersistentJobs);
        }

        return $allJobs;
    }
}
