<?php

namespace DigitalMarketingFramework\Distributer\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryInterface;
use DigitalMarketingFramework\Distributer\Core\DataDispatcher\DataDispatcherInterface;

interface DataDispatcherRegistryInterface extends PluginRegistryInterface
{
    public function registerDataDispatcher(string $class, array $additionalArguments = [], string $keyword = ''): void;
    public function getDataDispatchers(): array;
    public function getDataDispatcher(string $keyword): ?DataDispatcherInterface;
    public function deleteDataDispatcher(string $keyword): void;
}
