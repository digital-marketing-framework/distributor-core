<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Core\Queue\NonPersistentQueue;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Registry\RegistryException;

trait QueueRegistryTrait
{
    protected QueueInterface $persistentQueue;

    protected QueueInterface $nonPersistentQueue;

    public function getPersistentQueue(): QueueInterface
    {
        if (!isset($this->persistentQueue)) {
            throw new RegistryException('Persistent distributor job queue not defined.');
        }

        return $this->persistentQueue;
    }

    public function setPersistentQueue(QueueInterface $queue): void
    {
        $this->persistentQueue = $queue;
    }

    public function getNonPersistentQueue(): QueueInterface
    {
        if (!isset($this->nonPersistentQueue)) {
            $this->nonPersistentQueue = new NonPersistentQueue();
        }

        return $this->nonPersistentQueue;
    }

    public function setNonPersistentQueue(QueueInterface $queue): void
    {
        $this->nonPersistentQueue = $queue;
    }
}
