<?php

namespace DigitalMarketingFramework\Distributer\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryTrait;
use DigitalMarketingFramework\Distributer\Core\Route\RouteInterface;

trait RouteRegistryTrait
{
    use PluginRegistryTrait;

    public function registerRoute(string $class, array $additionalArguments = [], string $keyword = ''): void
    {
        $this->registerPlugin(RouteInterface::class, $class, $additionalArguments, $keyword);
    }

    public function getRoutes(): array
    {
        return $this->getAllPlugins(RouteInterface::class);
    }

    public function getRoute(string $keyword): ?RouteInterface
    {
        return $this->getPlugin($keyword, RouteInterface::class);
    }
    
    public function deleteRoute(string $keyword): void
    {
        $this->deletePlugin($keyword, RouteInterface::class);
    }

    public function getRouteDefaultConfigurations(): array
    {
        $result = [];
        foreach ($this->pluginClasses[RouteInterface::class] as $key => $class) {
            $result[$key] = $class::getDefaultConfiguration();
        }
        return $result;
    }
}
