<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataDispatcher;

use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcher;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;

class SpiedOnDataDispatcher extends DataDispatcher
{
    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        public DataDispatcherSpyInterface $spy,
    ) {
        parent::__construct($keyword, $registry);
    }

    public function send(array $data): void
    {
        $this->spy->send($data);
    }
}
