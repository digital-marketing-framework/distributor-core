<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataDispatcher;

use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcher;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;

class SpiedOnGenericDataDispatcher extends DataDispatcher
{
    public $spy;

    public function __construct(string $keyword, RegistryInterface $registry, DataDispatcherSpyInterface $spy)
    {
        parent::__construct($keyword, $registry);
        $this->spy = $spy;
    }

    public function send(array $data): bool
    {
        $this->spy->send($data);
        return true;
    }
}
