<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Spy\Route;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;

interface RouteSpyInterface extends DataDispatcherInterface
{
    public function addContext(ContextInterface $context): void;
}
