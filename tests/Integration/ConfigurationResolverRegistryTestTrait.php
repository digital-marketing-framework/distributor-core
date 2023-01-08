<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration;

use DigitalMarketingFramework\Core\Registry\Plugin\ConfigurationResolverRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\CorePluginInitialization;
use DigitalMarketingFramework\Distributor\Core\DistributorPluginInitialization;

trait ConfigurationResolverRegistryTestTrait // extends \DigitalMarketingFramework\Core\Tests\Integration\RegistryTestTrait
{
    protected ConfigurationResolverRegistryInterface $registry;

    protected function initRegistry(): void
    {
        parent::initRegistry();
        CorePluginInitialization::initialize($this->registry);
        DistributorPluginInitialization::initialize($this->registry);
    }
}
