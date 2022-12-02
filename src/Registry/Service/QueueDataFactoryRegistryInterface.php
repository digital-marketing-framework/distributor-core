<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactoryInterface;

interface QueueDataFactoryRegistryInterface
{
    public function getQueueDataFactory(): QueueDataFactoryInterface;
    public function setQueueDataFactory(QueueDataFactoryInterface $queueDataFactory): void;
}
