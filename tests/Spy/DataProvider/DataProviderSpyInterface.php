<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;

interface DataProviderSpyInterface
{
    public function processContext(ContextInterface $context): void;
    public function process(): void;
}
