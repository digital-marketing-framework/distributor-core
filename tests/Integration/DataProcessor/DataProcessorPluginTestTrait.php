<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration\DataProcessor;

use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Distributor\Core\DistributorCoreInitialization;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

trait DataProcessorPluginTestTrait
{
    protected function initRegistry(): void
    {
        parent::initRegistry();
        $initialization = new DistributorCoreInitialization();
        $initialization->init(RegistryDomain::CORE, $this->registry);
    }
}
