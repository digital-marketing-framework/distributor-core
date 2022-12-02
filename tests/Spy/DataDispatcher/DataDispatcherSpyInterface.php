<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataDispatcher;

interface DataDispatcherSpyInterface
{
    public function send(array $data): void;
}
