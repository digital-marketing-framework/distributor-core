<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\Spy\Route;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Distributer\Core\DataDispatcher\DataDispatcherInterface;

interface RouteSpyInterface extends DataDispatcherInterface
{
    public function addContext(ContextInterface $context): void;
}
