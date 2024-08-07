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
        // NOTE core initialization has no global config or services to initialize customly
        //      but other integrations will want to call those methods on their init object
        // $initialization->initGlobalConfiguration(RegistryDomain::CORE, $this->registry);
        // $initialization->initServices(RegistryDomain::CORE, $this->registry);
        $initialization->initPlugins(RegistryDomain::CORE, $this->registry);
    }
}
