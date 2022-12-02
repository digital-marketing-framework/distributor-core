<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\Spy\DataDispatcher;

use DigitalMarketingFramework\Distributer\Core\DataDispatcher\DataDispatcher;
use DigitalMarketingFramework\Distributer\Core\Registry\RegistryInterface;

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
