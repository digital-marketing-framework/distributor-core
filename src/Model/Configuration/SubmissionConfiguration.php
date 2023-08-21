<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\Configuration;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SwitchSchema;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Configuration\Configuration;
use DigitalMarketingFramework\Core\Utility\ListUtility;
use DigitalMarketingFramework\Distributor\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\Route\RouteSchema;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;

class SubmissionConfiguration extends Configuration implements SubmissionConfigurationInterface
{
    public function getDistributorConfiguration(bool $resolveNull = true): array
    {
        return $this->getMergedConfiguration($resolveNull)[static::KEY_DISTRIBUTOR] ?? [];
    }

    public function getDataProviderConfiguration(string $dataProviderName): array
    {
        return $this->getDistributorConfiguration()[static::KEY_DATA_PROVIDERS][$dataProviderName] ?? [];
    }

    public function getRouteIds(): array
    {
        return array_keys($this->getDistributorConfiguration()[static::KEY_ROUTES] ?? []);
    }

    protected function getRouteListItem(string $routeId): array
    {
        $routeList = $this->getDistributorConfiguration()[static::KEY_ROUTES] ?? [];
        if (!isset($routeList[$routeId])) {
            throw new DigitalMarketingFrameworkException(sprintf('route with ID %s not found', $routeId));
        }
        return $routeList[$routeId];
    }

    public function getRouteConfiguration(string $routeId): array
    {
        $routeItem = $this->getRouteListItem($routeId);
        $routeConfiguration = ListUtility::getItemValue($routeItem);
        return SwitchSchema::getSwitchConfiguration($routeConfiguration);
    }

    public function getRouteKeyword(string $routeId): string
    {
        $routeItem = $this->getRouteListItem($routeId);
        $routeConfiguration = ListUtility::getItemValue($routeItem);
        return SwitchSchema::getSwitchType($routeConfiguration);
    }

    public function getRouteLabel(string $routeId): string
    {
        $routeName = $this->getRouteKeyword($routeId);

        $routePassCount = 0;
        $routePassIndex = 0;
        foreach ($this->getRouteIds() as $currentRouteId) {
            if ($this->getRouteKeyword($currentRouteId) === $routeName) {
                $routePassCount++;
            }
            if ($routeId === $currentRouteId) {
                $routePassIndex = $routePassCount;
            }
        }

        if ($routePassCount === 1) {
            return $routeName;
        }

        $routeConfig = ListUtility::getItemValue($this->getRouteListItem($routeId));
        if ($routeConfig[RouteSchema::KEY_PASS] !== '') {
            return $routeName . '#' . $routeConfig[RouteSchema::KEY_PASS];
        }

        return $routeName . '#' . $routePassIndex;
    }
}
