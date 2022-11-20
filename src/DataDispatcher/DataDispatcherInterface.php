<?php

namespace DigitalMarketingFramework\Distributor\Core\DataDispatcher;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Plugin\PluginInterface;

interface DataDispatcherInterface extends PluginInterface
{
    /**
     * @throws DigitalMarketingFrameworkException
     */
    public function send(array $data): void;
}
