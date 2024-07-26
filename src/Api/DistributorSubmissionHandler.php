<?php

namespace DigitalMarketingFramework\Distributor\Core\Api;

use DigitalMarketingFramework\Core\Api\ApiException;
use DigitalMarketingFramework\Core\Api\EndPoint\EndPointStorageAwareInterface;
use DigitalMarketingFramework\Core\Api\EndPoint\EndPointStorageAwareTrait;
use DigitalMarketingFramework\Core\ConfigurationDocument\ConfigurationDocumentManagerInterface;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\WriteableContext;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Log\LoggerAwareInterface;
use DigitalMarketingFramework\Core\Log\LoggerAwareTrait;
use DigitalMarketingFramework\Core\Model\Api\EndPointInterface;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfiguration;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Service\DistributorInterface;

class DistributorSubmissionHandler implements DistributorSubmissionHandlerInterface, LoggerAwareInterface, EndPointStorageAwareInterface
{
    use LoggerAwareTrait;
    use EndPointStorageAwareTrait;

    protected ConfigurationDocumentManagerInterface $configurationDocumentManager;

    protected DistributorInterface $distributor;

    public function __construct(
        protected RegistryInterface $registry,
    ) {
        $this->configurationDocumentManager = $registry->getConfigurationDocumentManager();
        $this->distributor = $registry->getDistributor();
    }

    protected function handleException(string|DigitalMarketingFrameworkException $error, ?int $code = null): never
    {
        $message = $error;
        $exception = null;
        if ($error instanceof DigitalMarketingFrameworkException) {
            $message = $error->getMessage();
            $exception = $error;
        }

        $this->logger->error($message);
        throw new ApiException($message, $code ?? 500, $exception);
    }

    public function submit(
        array|DistributorConfigurationInterface $configuration,
        array|DataInterface $data,
        array|ContextInterface|null $context = null,
        bool $responsive = false
    ): void {
        if (is_array($context)) {
            $context = new WriteableContext($context);
        }

        try {
            if ($context instanceof ContextInterface) {
                $this->registry->pushContext($context);
            }

            $submission = new SubmissionDataSet($data, $configuration);
            $submission->getContext()->setResponsive($responsive);
            $jobs = $this->distributor->process($submission);
            foreach ($jobs as $job) {
                if ($job->getStatus() === QueueInterface::STATUS_FAILED) {
                    throw new DigitalMarketingFrameworkException($job->getStatusMessage());
                }
            }

            if ($submission->getContext()->isResponsive()) {
                $submission->getContext()->applyResponseData();
            }

            if ($context instanceof ContextInterface) {
                $this->registry->popContext();
            }
        } catch (DigitalMarketingFrameworkException $e) {
            if ($context instanceof ContextInterface) {
                $this->registry->popContext();
            }

            $this->handleException($e);
        }
    }

    public function submitToEndPoint(
        EndPointInterface $endPoint,
        array|DataInterface $data,
        array|ContextInterface|null $context = null
    ): void {
        if (!$endPoint->getEnabled()) {
            $this->handleException('End point not found or disabled', 404);
        }

        $allowOverride = !$endPoint->getDisableContext() && $endPoint->getAllowContextOverride();
        if ($context !== null && !$allowOverride) {
            $this->logger->info(sprintf('Blocked attempt to override context for the end point "%s", which did not allow overrides.', $endPoint->getName()));
        }

        if ($endPoint->getDisableContext()) {
            $context = new WriteableContext();
        } elseif (!$endPoint->getAllowContextOverride()) {
            $context = null;
        }

        try {
            $configurationDocument = $endPoint->getConfigurationDocument();
            $configurationStack = $this->configurationDocumentManager->getConfigurationStackFromDocument($configurationDocument);
            $configuration = new DistributorConfiguration($configurationStack);
        } catch (DigitalMarketingFrameworkException $e) {
            $this->handleException($e);
        }

        $this->submit($configuration, $data, $context, responsive: !$endPoint->getDisableContext());
    }

    public function getEndPointNames(bool $frontend = false): array
    {
        $names = [];
        foreach ($this->endPointStorage->getAllEndPoints() as $endPoint) {
            if (!$endPoint->getEnabled()) {
                continue;
            }

            if (!$endPoint->getPushEnabled()) {
                continue;
            }

            if ($frontend && !$endPoint->getExposeToFrontend()) {
                continue;
            }

            $names[] = $endPoint->getName();
        }

        return $names;
    }
}
