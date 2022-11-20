<?php

namespace DigitalMarketingFramework\Distributer\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryTrait;
use DigitalMarketingFramework\Distributer\Core\DataProvider\DataProviderInterface;

trait DataProviderRegistryTrait
{
    use PluginRegistryTrait;

    public function registerDataProvider(string $class, array $additionalArguments = [], string $keyword = ''): void
    {
        $this->registerPlugin(DataProviderInterface::class, $class, $additionalArguments, $keyword);
    }
    
    public function getDataProvider(string $keyword): ?DataProviderInterface
    {
        return $this->getPlugin($keyword, DataProviderInterface::class);
    }

    public function getDataProviders(): array
    {
        return $this->getAllPlugins(DataProviderInterface::class);
    }

    public function deleteDataProvider(string $keyword): void
    {
        $this->deletePlugin($keyword, DataProviderInterface::class);
    }

    public function getDataProviderDefaultConfigurations(): array
    {
        $result = [];
        foreach ($this->pluginClasses[DataProviderInterface::class] ?? [] as $key => $class) {
            $result[$key] = $class::getDefaultConfiguration();
        }
        return $result;
    }
}
