<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\Integration;

use DigitalMarketingFramework\Distributer\Core\Tests\Spy\DataProvider\DataProviderSpyInterface;
use DigitalMarketingFramework\Distributer\Core\Tests\Spy\Route\RouteSpyInterface;
use PHPUnit\Framework\MockObject\MockObject;

trait RelayTestTrait
{
    use RegistryTestTrait;
    use SubmissionTestTrait;
    use JobTestTrait;

    protected function initRelay(): void
    {
        $this->initRegistry();
        $this->initSubmission();
    }

    protected function addRouteSpy(array $configuration): RouteSpyInterface&MockObject
    {
        $spy = $this->registerRouteSpy();
        $this->addRouteConfiguration('generic', $configuration);
        return $spy;
    }

    protected function addDataProviderSpy(array $configuration): DataProviderSpyInterface&MockObject
    {
        $spy = $this->registerDataProviderSpy();
        $this->addDataProviderConfiguration('generic', $configuration);
        return $spy;
    }
}
