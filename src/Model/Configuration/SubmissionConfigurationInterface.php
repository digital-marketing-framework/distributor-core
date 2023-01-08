<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\Configuration;

use DigitalMarketingFramework\Core\Model\Configuration\ConfigurationInterface;

interface SubmissionConfigurationInterface extends ConfigurationInterface
{
    public const KEY_DISTRIBUTOR = 'distributor';

    public const KEY_DATA_PROVIDERS = 'dataProviders';
    public const KEY_ROUTES = 'routes';
    public const KEY_ROUTE_PASSES = 'passes';

    public function getDistributorConfiguration(): array;

    public function getWithRoutePassOverride(string $key, string $route, int $pass, $default = null): mixed;

    public function dataProviderExists(string $dataProviderName): bool;
    public function getDataProviderConfiguration(string $dataProviderName): array;

    public function routeExists(string $routeName): bool;
    public function routePassExists(string $routeName, int $pass): bool;
    public function getRoutePassCount(string $routeName): int;
    public function getRoutePassConfiguration(string $routeName, int $pass): array;
    public function getRoutePassLabel(string $routeName, int $pass): string;
}
