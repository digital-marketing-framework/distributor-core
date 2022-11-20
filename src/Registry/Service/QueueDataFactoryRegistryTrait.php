<?php

namespace DigitalMarketingFramework\Distributer\Core\Registry\Service;

use DigitalMarketingFramework\Distributer\Core\Factory\QueueDataFactoryInterface;

trait QueueDataFactoryRegistryTrait
{
    protected QueueDataFactoryInterface $queueDataFactory;

    public function getQueueDataFactory(): QueueDataFactoryInterface
    {
        return $this->queueDataFactory;
    }

    public function setQueueDataFactory(QueueDataFactoryInterface $queueDataFactory): void
    {
        $this->queueDataFactory = $queueDataFactory;
    }
}
