<?php

namespace DigitalMarketingFramework\Distributer\Core\Registry\Service;

use DigitalMarketingFramework\Distributer\Core\Factory\QueueDataFactoryInterface;

interface QueueDataFactoryRegistryInterface
{
    public function getQueueDataFactory(): QueueDataFactoryInterface;
    public function setQueueDataFactory(QueueDataFactoryInterface $queueDataFactory): void;
}
