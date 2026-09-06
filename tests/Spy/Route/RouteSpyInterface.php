<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Spy\Route;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataDispatcher\DataDispatcherSpyInterface;

interface RouteSpyInterface extends DataDispatcherSpyInterface
{
    public function addContext(ContextInterface $context): void;
}
