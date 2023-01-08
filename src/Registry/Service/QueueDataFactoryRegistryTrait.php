<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Core\Registry\Service\ConfigurationDocumentManagerRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactoryInterface;

trait QueueDataFactoryRegistryTrait
{
    use ConfigurationDocumentManagerRegistryTrait;

    protected QueueDataFactoryInterface $queueDataFactory;

    public function getQueueDataFactory(): QueueDataFactoryInterface
    {
        if (!isset($this->queueDataFactory)) {
            $configurationDocumentManager = $this->getConfigurationDocumentManager();
            $this->queueDataFactory = new QueueDataFactory($configurationDocumentManager);
        }
        return $this->queueDataFactory;
    }

    public function setQueueDataFactory(QueueDataFactoryInterface $queueDataFactory): void
    {
        $this->queueDataFactory = $queueDataFactory;
    }
}
