<?php

namespace DigitalMarketingFramework\Distributor\Core\DataDispatcher;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Core\Plugin\PluginInterface;

interface DataDispatcherInterface extends PluginInterface
{
    /**
     * @param array<string,string|ValueInterface> $data
     *
     * @throws DigitalMarketingFrameworkException
     */
    public function send(array $data): void;

    /**
     * @param array<string,string|ValueInterface> $data
     *
     * @throws DigitalMarketingFrameworkException
     */
    public function preview(array $data): string;
}
