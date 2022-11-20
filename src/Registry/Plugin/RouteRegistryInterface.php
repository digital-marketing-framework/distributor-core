<?php

namespace DigitalMarketingFramework\Distributer\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryInterface;
use DigitalMarketingFramework\Distributer\Core\Route\RouteInterface;

interface RouteRegistryInterface extends PluginRegistryInterface
{
    public function registerRoute(string $class, array $additionalArguments = [], string $keyword = ''): void;
    public function getRoutes(): array;
    public function getRoute(string $keyword): ?RouteInterface;
    public function deleteRoute(string $keyword): void;
    public function getRouteDefaultConfigurations(): array;
}
