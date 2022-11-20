<?php

namespace DigitalMarketingFramework\Distributer\Core\DataDispatcher;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Plugin\PluginInterface;

interface DataDispatcherInterface extends PluginInterface
{
    /**
     * @param array $data
     * @throws DigitalMarketingFrameworkException
     */
    public function send(array $data);
}
