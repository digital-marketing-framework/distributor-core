<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Core\ConfigurationDocument\ConfigurationDocumentManagerInterface;
use DigitalMarketingFramework\Core\Registry\Service\ConfigurationDocumentManagerRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactoryInterface;

trait QueueDataFactoryRegistryTrait
{
    use ConfigurationDocumentManagerRegistryTrait;

    abstract public function getConfigurationDocumentManager(): ConfigurationDocumentManagerInterface;

    protected QueueDataFactoryInterface $queueDataFactory;

    public function getQueueDataFactory(): QueueDataFactoryInterface
    {
        if (!isset($this->queueDataFactory)) {
            $configurationDocumentManager = $this->getConfigurationDocumentManager();
            $this->queueDataFactory = $this->createObject(QueueDataFactory::class, [$configurationDocumentManager]);
        }
        return $this->queueDataFactory;
    }

    public function setQueueDataFactory(QueueDataFactoryInterface $queueDataFactory): void
    {
        $this->queueDataFactory = $queueDataFactory;
    }
}
