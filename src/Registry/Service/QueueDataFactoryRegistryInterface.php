<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Core\Registry\Service\ConfigurationDocumentManagerRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactoryInterface;

interface QueueDataFactoryRegistryInterface extends ConfigurationDocumentManagerRegistryInterface
{
    public function getQueueDataFactory(): QueueDataFactoryInterface;
    public function setQueueDataFactory(QueueDataFactoryInterface $queueDataFactory): void;
}
