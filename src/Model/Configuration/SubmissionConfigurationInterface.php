<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\Configuration;

use DigitalMarketingFramework\Core\Model\Configuration\ConfigurationInterface;

interface SubmissionConfigurationInterface extends ConfigurationInterface
{
    public const KEY_DISTRIBUTOR = 'distributor';

    public const KEY_DATA_PROVIDERS = 'dataProviders';

    public const KEY_ROUTES = 'routes';

    /**
     * @return array<string,mixed>
     */
    public function getDistributorConfiguration(): array;

    /**
     * @return array<string,mixed>
     */
    public function getDataProviderConfiguration(string $dataProviderName): array;

    /**
     * @return array<string>
     */
    public function getRouteIds(): array;

    /**
     * @return array<string,mixed>
     */
    public function getRouteConfiguration(string $routeId): array;

    public function getRouteKeyword(string $routeId): string;

    public function getRouteLabel(string $routeId): string;
}
