<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataDispatcher;

use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;

interface DataDispatcherSpyInterface
{
    /**
     * @param array<string,string|ValueInterface> $data
     */
    public function send(array $data): void;
}
