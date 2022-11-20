<?php

namespace DigitalMarketingFramework\Distributer\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryInterface;
use DigitalMarketingFramework\Distributer\Core\DataProvider\DataProviderInterface;

interface DataProviderRegistryInterface extends PluginRegistryInterface
{
    public function registerDataProvider(string $class, array $additionalArguments = [], string $keyword = ''): void;
    public function getDataProvider(string $keyword): ?DataProviderInterface;
    public function getDataProviders(): array;
    public function getDataProviderDefaultConfigurations(): array;
    public function deleteDataProvider(string $keyword): void;
}
