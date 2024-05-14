<?php

namespace DigitalMarketingFramework\Distributor\Core\Api;

use DigitalMarketingFramework\Core\Api\ApiException;
use DigitalMarketingFramework\Core\ConfigurationDocument\ConfigurationDocumentManagerInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Log\LoggerAwareInterface;
use DigitalMarketingFramework\Core\Log\LoggerAwareTrait;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Distributor\Core\Api\EndPoint\EndPointStorageInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Api\EndPointInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfiguration;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Service\DistributorInterface;

class DistributorSubmissionHandler implements DistributorSubmissionHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected ConfigurationDocumentManagerInterface $configurationDocumentManager;

    protected EndPointStorageInterface $endPointStorage;

    protected DistributorInterface $distributor;

    public function __construct(
        protected RegistryInterface $registry,
    ){
        $this->configurationDocumentManager = $registry->getConfigurationDocumentManager();
        $this->distributor = $registry->getDistributor();
        $this->endPointStorage = $registry->getEndPointStorage();
    }

    protected function handleException(string|DigitalMarketingFrameworkException $error): never
    {
        $message = $error;
        $exception = null;
        if ($error instanceof DigitalMarketingFrameworkException) {
            $message = $error->getMessage();
            $exception = $error;
        }
        $this->logger->error($message);
        throw new ApiException($message, 500, $exception);
    }

    public function submit(array|DistributorConfigurationInterface $configuration, array|DataInterface $data): void
    {
        try {
            $submission = new SubmissionDataSet($data, $configuration);
            $this->distributor->process($submission);
        } catch (DigitalMarketingFrameworkException $e) {
            $this->handleException($e);
        }
    }

    public function submitToEndPoint(EndPointInterface $endPoint, array|DataInterface $data): void
    {
        try {
            $configurationDocument = $endPoint->getConfigurationDocument();
            $configurationStack = $this->configurationDocumentManager->getConfigurationStackFromDocument($configurationDocument);
            $configuration = new DistributorConfiguration($configurationStack);
        } catch (DigitalMarketingFrameworkException $e) {
            $this->handleException($e);
        }

        $this->submit($configuration, $data);
    }

    public function submitToEndPointBySegment(string $endPointSegment, array|DataInterface $data): void
    {
        try {
            $endPoint = $this->endPointStorage->getEndPointFromSegment($endPointSegment);
        } catch (DigitalMarketingFrameworkException $e) {
            $this->handleException($e);
        }

        if (!$endPoint instanceof EndPointInterface) {
            $this->handleException(sprintf('No end point "%s" found.', $endPointSegment));
        }

        $this->submitToEndPoint($endPoint, $data);
    }

    public function getEndpointSegments(): array
    {
        return array_map(function(EndPointInterface $endPoint) {
            return $endPoint->getPathSegment();
        }, $this->endPointStorage->getAllEndPoints());
    }
}
