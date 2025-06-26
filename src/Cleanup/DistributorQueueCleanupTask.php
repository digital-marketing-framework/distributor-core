<?php

namespace DigitalMarketingFramework\Distributor\Core\Cleanup;

use DigitalMarketingFramework\Core\Cleanup\QueueCleanupTask;
use DigitalMarketingFramework\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface as DistributorRegistryInterface;

class DistributorQueueCleanupTask extends QueueCleanupTask
{
    public function __construct(string $keyword, RegistryInterface $registry)
    {
        $distibutorRegistry = $registry->getRegistryCollection()->getRegistryByClass(DistributorRegistryInterface::class);

        $queueProcessor = $distibutorRegistry->getQueueProcessor(
            $distibutorRegistry->getPersistentQueue(),
            $distibutorRegistry->getDistributor()
        );

        parent::__construct($keyword, $queueProcessor);
    }
}
