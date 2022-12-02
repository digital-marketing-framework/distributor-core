<?php

namespace DigitalMarketingFramework\Distributer\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributer\Core\Route\RouteInterface;

interface RouteRegistryInterface extends PluginRegistryInterface
{
    public function registerRoute(string $class, array $additionalArguments = [], string $keyword = ''): void;
    
    /**
     * @return array<RouteInterface>
     */
    public function getRoutes(SubmissionDataSetInterface $submission): array;
    public function getRoute(string $keyword, SubmissionDataSetInterface $submission, int $pass): ?RouteInterface;
    public function deleteRoute(string $keyword): void;
    
    /**
     * @return array<mixed>
     */
    public function getRouteDefaultConfigurations(): array;
}
