<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\Spy\DataDispatcher;

interface DataDispatcherSpyInterface
{
    public function send(array $data): void;
}
