<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;

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
