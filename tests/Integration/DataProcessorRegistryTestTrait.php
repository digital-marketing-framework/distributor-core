<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration;

use DigitalMarketingFramework\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\CorePluginInitialization;
use DigitalMarketingFramework\Distributor\Core\DistributorPluginInitialization;

trait DataProcessorRegistryTestTrait // extends \DigitalMarketingFramework\Core\Tests\Integration\RegistryTestTrait
{
    protected RegistryInterface $registry;

    protected function initRegistry(): void
    {
        parent::initRegistry();
        CorePluginInitialization::initialize($this->registry);
        DistributorPluginInitialization::initialize($this->registry);
    }
}
