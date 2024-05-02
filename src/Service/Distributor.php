<?php

namespace DigitalMarketingFramework\Distributor\Core\Service;

use DigitalMarketingFramework\Core\Context\ContextAwareInterface;
use DigitalMarketingFramework\Core\Context\ContextAwareTrait;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Log\LoggerAwareInterface;
use DigitalMarketingFramework\Core\Log\LoggerAwareTrait;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Queue\QueueException;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\RestrictedTermsSchema;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactoryInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRouteInterface;

class Distributor implements DistributorInterface, LoggerAwareInterface, ContextAwareInterface
{
    use LoggerAwareTrait;
    use ContextAwareTrait;

    protected QueueInterface $persistentQueue;

    protected QueueInterface $temporaryQueue;

    protected QueueDataFactoryInterface $queueDataFactory;

    public function __construct(protected RegistryInterface $registry)
    {
        $this->persistentQueue = $registry->getPersistentQueue();
        $this->temporaryQueue = $registry->getNonPersistentQueue();
        $this->queueDataFactory = $registry->getQueueDataFactory();
    }

    protected function addContext(SubmissionDataSetInterface $submission): void
    {
        $dataProviders = $this->registry->getDataProviders($submission);
        foreach ($dataProviders as $dataProvider) {
            try {
                $dataProvider->addContext($this->context);
            } catch (DigitalMarketingFrameworkException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        $routes = $this->registry->getOutboundRoutes($submission);
        foreach ($routes as $route) {
            try {
                $route->addContext($this->context);
            } catch (DigitalMarketingFrameworkException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @param array<string> $enabledDataProviders
     */
    protected function processDataProviders(SubmissionDataSetInterface $submission, array $enabledDataProviders = ['*']): void
    {
        $dataProviders = $this->registry->getDataProviders($submission);
        foreach ($dataProviders as $dataProvider) {
            $keyword = $dataProvider->getKeyword();
            if (RestrictedTermsSchema::isTermAllowed($enabledDataProviders, $keyword)) {
                $dataProvider->addData();
            }
        }
    }

    public function processJob(JobInterface $job): bool
    {
        try {
            $submission = $this->queueDataFactory->convertJobToSubmission($job);

            $routeId = $this->queueDataFactory->getJobRouteId($job);
            $integrationName = $this->queueDataFactory->getJobRouteIntegrationName($job);
            $route = $this->registry->getOutboundRoute($submission, $integrationName, $routeId);
            if (!$route instanceof OutboundRouteInterface) {
                throw new DigitalMarketingFrameworkException(sprintf('route with ID "%s" not found in integration "%s"', $routeId, $integrationName));
            }

            $this->processDataProviders($submission, $route->getEnabledDataProviders());

            return $route->process();
        } catch (DigitalMarketingFrameworkException $e) {
            $this->logger->error($e->getMessage());
            throw new QueueException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function process(SubmissionDataSetInterface $submission): void
    {
        $this->addContext($submission);

        $syncPersistentJobs = [];
        $syncTemporaryJobs = [];
        $routes = $this->registry->getOutboundRoutes($submission);

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
                $route::getIntegrationName(),
                $route->getRouteId(),
                $status
            );
            $job = $queue->addJob($job);
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
    }
}
