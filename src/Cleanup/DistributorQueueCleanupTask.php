<?php

namespace DigitalMarketingFramework\Distributor\Core\Cleanup;

use DigitalMarketingFramework\Core\Cleanup\QueueCleanupTask;
use DigitalMarketingFramework\Core\Queue\GlobalConfiguration\Settings\QueueSettings;
use DigitalMarketingFramework\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Queue\GlobalConfiguration\Settings\QueueSettings as DistributorQueueSettings;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface as DistributorRegistryInterface;

class DistributorQueueCleanupTask extends QueueCleanupTask
{
    public function __construct(string $keyword, RegistryInterface $registry)
    {
        $distibutorRegistry = $registry->getRegistryCollection()->getRegistryByClass(DistributorRegistryInterface::class);
        parent::__construct($keyword, $distibutorRegistry->getPersistentQueue());
    }

    protected function getQueueSettings(): QueueSettings
    {
        return $this->globalConfiguration->getGlobalSettings(DistributorQueueSettings::class);
    }
}
