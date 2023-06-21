<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\Configuration;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Configuration\Configuration;

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

    protected function getRouteConfiguration(): array
    {
        return $this->getDistributorConfiguration()[static::KEY_ROUTES];
    }

    /**
     * @return array<array<int,string|int>>
     * array<int,array{keyword: string, pass: int, name: string, configuration: array<mixed>}>
     */
    public function getRoutePasses(): array
    {
        $result = [];
        $passCountPerRoute = [];
        foreach ($this->getRouteConfiguration() as $routeConfiguration) {
            $keyword = $routeConfiguration['type'];
            $pass = $passCountPerRoute[$keyword] ?? 0;
            $passCountPerRoute[$keyword] = $pass + 1;
            $name = $routeConfiguration['name'] ?? '';
            $config = $routeConfiguration['config'][$keyword] ?? [];
            $result[] = [
                'keyword' => $keyword,
                'pass' => $pass,
                'name' => $name,
                'configuration' => $config,
            ];
        }
        return $result;
    }

    public function getRoutePassCount(string $routeName = ''): int
    {
        $count = 0;
        foreach ($this->getRoutePasses() as $routeData) {
            if ($routeName === '' || $routeData['keyword'] === $routeName) {
                $count++;
            }
        }
        return $count;
    }

    public function getRoutePassLabel(int $index): string
    {
        $routeDataList = $this->getRoutePasses();
        if (!isset($routeDataList[$index])) {
            throw new DigitalMarketingFrameworkException(sprintf('route configuration for index %d not found', $index));
        }
        $routeData = $routeDataList[$index];
        $routeName = $routeData['keyword'];
        $passCount = $this->getRoutePassCount($routeName);
        $label = $routeName;
        if ($passCount > 1) {
            $label .= '#' . ($routeData['name'] ?: ($routeData['pass'] + 1));
        }
        return $label;
    }

    public function getRoutePassData(int $index): array
    {
        $routeDataList = $this->getRoutePasses();
        if (!isset($routeDataList[$index])) {
            throw new DigitalMarketingFrameworkException(sprintf('route configuration for index %s not found', $index));
        }
        return $routeDataList[$index];
    }

    public function getRouteName(int $index): string
    {
        return $this->getRoutePassData($index)['keyword'];
    }

    public function getRoutePass(int $index): int
    {
        return $this->getRoutePassData($index)['pass'];
    }

    public function getRoutePassConfiguration(int $index): array
    {
        return $this->getRoutePassData($index)['configuration'];
    }
}
