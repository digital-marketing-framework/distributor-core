<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration\DataProcessor;

use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Distributor\Core\DistributorCoreInitialization;

trait DataProcessorPluginTestTrait
{
    protected function initRegistry(): void
    {
        parent::initRegistry();
        $initialization = new DistributorCoreInitialization();
        $initialization->initMetaData($this->registry);
        $initialization->initGlobalConfiguration(RegistryDomain::CORE, $this->registry);
        $initialization->initServices(RegistryDomain::CORE, $this->registry);
        $initialization->initPlugins(RegistryDomain::CORE, $this->registry);
    }
}
