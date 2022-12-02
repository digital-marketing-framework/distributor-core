<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration;

use DigitalMarketingFramework\Distributor\Core\ConfigurationResolverInitialization;
use DigitalMarketingFramework\Core\Registry\Plugin\ConfigurationResolverRegistryInterface;

trait ConfigurationResolverRegistryTestTrait // extends \DigitalMarketingFramework\Core\Tests\Integration\RegistryTestTrait
{
    protected ConfigurationResolverRegistryInterface $registry;

    protected function initRegistry(): void
    {
        parent::initRegistry();
        ConfigurationResolverInitialization::initialize($this->registry);
    }
}
