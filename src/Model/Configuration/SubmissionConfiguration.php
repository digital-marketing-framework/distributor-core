<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\Configuration;

use DigitalMarketingFramework\Core\Model\Configuration\Configuration;
use DigitalMarketingFramework\Core\Utility\ConfigurationUtility;

class SubmissionConfiguration extends Configuration implements SubmissionConfigurationInterface
{
    public function getDistributorConfiguration(bool $resolveNull = true): array
    {
        return $this->getMergedConfiguration($resolveNull)[static::KEY_DISTRIBUTOR] ?? [];
    }

    public function getWithRoutePassOverride(string $key, string $route, int $pass, mixed $default = null): mixed
    {
        $value = $this->getRoutePassConfiguration($route, $pass)[$key] ?? null;
        if ($value !== null) {
            return $value;
        }
        return $this->getDistributorConfiguration()[$key] ?? $default;
    }

    public function getDataProviderConfiguration(string $dataProviderName): array
    {
        return $this->getDistributorConfiguration()[static::KEY_DATA_PROVIDERS][$dataProviderName] ?? [];
    }

    public function dataProviderExists(string $dataProviderName): bool
    {
        return isset($this->getDistributorConfiguration()[static::KEY_DATA_PROVIDERS][$dataProviderName]);
    }

    protected function getRouteConfiguration(string $routeName): array
    {
        $rawConfiguration = $this->getDistributorConfiguration(false)[static::KEY_ROUTES][$routeName] ?? [];

        $baseConfiguration = $rawConfiguration;
        unset($baseConfiguration[static::KEY_ROUTE_PASSES]);

        $passConfigurations = [[]];
        if (isset($rawConfiguration[static::KEY_ROUTE_PASSES]) && $rawConfiguration[static::KEY_ROUTE_PASSES]) {
            $passConfigurations = $rawConfiguration[static::KEY_ROUTE_PASSES];
        }

        $configuration = [];
        foreach ($passConfigurations as $key => $passConfiguration) {
            $passBaseConfiguration = $baseConfiguration;
            $configuration[$key] = ConfigurationUtility::mergeConfiguration($passBaseConfiguration, $passConfiguration, false);
        }
        $configuration = ConfigurationUtility::resolveNullInMergedConfiguration($configuration);
        ksort($configuration);
        ksort($configuration, SORT_NUMERIC);
        return $configuration;
    }

    protected function getRoutePassName(string $routeName, int $pass): string
    {
        $keys = array_keys($this->getRouteConfiguration($routeName));
        if (!isset($keys[$pass]) || is_numeric($keys[$pass])) {
            return $pass + 1;
        }
        return $keys[$pass];
    }

    public function getRoutePassLabel(string $routeName, int $pass): string
    {
        $label = $routeName;
        $passName = $this->getRoutePassName($routeName, $pass);
        if (!is_numeric($passName) || $this->getRoutePassCount($routeName) > 1 || $pass > 0) {
            $label .= '#' . $passName;
        }
        return $label;
    }

    public function getRoutePassCount(string $routeName): int
    {
        if (!$this->routeExists($routeName)) {
            return 0;
        }
        $configuration = $this->getRouteConfiguration($routeName);
        return count($configuration);
    }

    public function getRoutePassConfiguration(string $routeName, int $pass): array
    {
        $configuration = array_values($this->getRouteConfiguration($routeName));
        return $configuration[$pass];
    }

    public function routeExists(string $routeName): bool
    {
        return isset($this->getDistributorConfiguration()[static::KEY_ROUTES][$routeName]);
    }

    public function routePassExists(string $routeName, int $pass): bool
    {
        return $pass >= 0 && $this->getRoutePassCount($routeName) > $pass;
    }
}
