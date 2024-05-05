<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\Configuration;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Configuration\Configuration;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SwitchSchema;
use DigitalMarketingFramework\Core\Utility\ListUtility;
use DigitalMarketingFramework\Distributor\Core\SchemaDocument\Schema\Plugin\Route\OutboundRouteSchema;

class DistributorConfiguration extends Configuration implements DistributorConfigurationInterface
{
    public function getOutboundConfiguration(string $integrationName): array
    {
        return $this->getIntegrationConfiguration($integrationName)[static::KEY_OUTBOUND_ROUTES] ?? [];
    }

    public function getGeneralOutboundConfiguration(): array
    {
        return $this->getOutboundConfiguration(static::KEY_GENERAL_INTEGRATION);
    }

    public function async(): bool
    {
        return $this->getGeneralOutboundConfiguration()[static::KEY_ASYNC] ?? static::DEFAULT_ASYNC;
    }

    public function enableStorage(): bool
    {
        return $this->getGeneralOutboundConfiguration()[static::KEY_ENABLE_STORAGE] ?? static::DEFAULT_ENABLE_STORAGE;
    }

    /**
     * @return array<string,mixed>
     */
    public function getDataProviderConfiguration(string $dataProviderName): array
    {
        return $this->getDataProcessingConfiguration()[static::KEY_DATA_PROVIDERS][$dataProviderName] ?? [];
    }

    /**
     * @return array<string,array<string>>
     */
    public function getOutboundRouteIds(): array
    {
        $routeIds = [];
        foreach ($this->getAllIntegrationConfigurations() as $integrationName => $integrationConfig) {
            if ($integrationName === static::KEY_GENERAL_INTEGRATION) {
                continue;
            }

            $routeIds[$integrationName] = ListUtility::getIdsSorted($integrationConfig[static::KEY_OUTBOUND_ROUTES] ?? []);
        }

        return $routeIds;
    }

    /**
     * @return array{uuid:string,weight:int,value:array{type:string,config:array<string,array<string,mixed>>}}
     */
    protected function getOutboundRouteListItem(string $integrationName, string $routeId): array
    {
        $routeList = $this->getOutboundConfiguration($integrationName);
        if (!isset($routeList[$routeId])) {
            throw new DigitalMarketingFrameworkException(sprintf('route with ID %s not found', $routeId));
        }

        return $routeList[$routeId];
    }

    /**
     * @return array<string,mixed>
     */
    public function getOutboundRouteConfiguration(string $integrationName, string $routeId): array
    {
        $routeItem = $this->getOutboundRouteListItem($integrationName, $routeId);
        $routeConfiguration = ListUtility::getItemValue($routeItem);

        return SwitchSchema::getSwitchConfiguration($routeConfiguration);
    }

    public function getOutboundRouteKeyword(string $integrationName, string $routeId): string
    {
        $routeItem = $this->getOutboundRouteListItem($integrationName, $routeId);
        $routeConfiguration = ListUtility::getItemValue($routeItem);

        return SwitchSchema::getSwitchType($routeConfiguration);
    }

    public function getOutboundRouteLabel(string $integrationName, string $routeId): string
    {
        $routeName = $this->getOutboundRouteKeyword($integrationName, $routeId);

        $routePassCount = 0;
        $routePassIndex = 0;
        foreach ($this->getOutboundRouteIds()[$integrationName] ?? [] as $currentRouteId) {
            if ($this->getOutboundRouteKeyword($integrationName, $currentRouteId) === $routeName) {
                ++$routePassCount;
            }

            if ($routeId === $currentRouteId) {
                $routePassIndex = $routePassCount;
            }
        }

        if ($routePassCount === 1) {
            return $routeName;
        }

        $routeConfig = ListUtility::getItemValue($this->getOutboundRouteListItem($integrationName, $routeId));
        if ($routeConfig[OutboundRouteSchema::KEY_PASS] !== '') {
            return $routeName . '#' . $routeConfig[OutboundRouteSchema::KEY_PASS];
        }

        return $routeName . '#' . $routePassIndex;
    }
}
