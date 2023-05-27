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

    public function getDataProviderConfiguration(string $dataProviderName): array;

    /**
     * @return array<array<int,string|int>>
     * array<int,array{keyword: string, pass: int, name: string, configuration: array<mixed>}>
     */
    public function getRoutePasses(): array;
    public function getRoutePassData(int $index): array;
    public function getRoutePassConfiguration(int $index): array;
    public function getRoutePassLabel(int $index): string;
}
