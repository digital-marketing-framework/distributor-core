<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\Configuration;

use DigitalMarketingFramework\Core\Model\Configuration\ConfigurationInterface;

interface DistributorConfigurationInterface extends ConfigurationInterface
{
    public const KEY_DATA_PROVIDERS = 'dataProviders';

    public const KEY_OUTBOUND_ROUTES = 'outboundRoutes';

    public const KEY_ENABLE_STORAGE = 'enableStorage';

    public const DEFAULT_ENABLE_STORAGE = false;

    public const KEY_ASYNC = 'async';

    public const DEFAULT_ASYNC = false;

    /**
     * @return array<string,mixed>
     */
    public function getOutboundConfiguration(string $integrationName): array;

    /**
     * @return array<string,mixed>
     */
    public function getGeneralOutboundConfiguration(): array;

    public function async(): bool;

    public function enableStorage(): bool;

    /**
     * @return array<string,mixed>
     */
    public function getDataProviderConfiguration(string $dataProviderName): array;

    /**
     * @return array<string,array<string>>
     */
    public function getOutboundRouteIds(): array;

    /**
     * @return array<string,mixed>
     */
    public function getOutboundRouteConfiguration(string $integrationName, string $routeId): array;

    public function getOutboundRouteKeyword(string $integrationName, string $routeId): string;

    public function getOutboundRouteLabel(string $integrationName, string $routeId): string;
}
