<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\Route\RouteSchema;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;

trait RouteRegistryTrait
{
    use PluginRegistryTrait;

    public function registerRoute(string $class, array $additionalArguments = [], string $keyword = ''): void
    {
        $this->registerPlugin(RouteInterface::class, $class, $additionalArguments, $keyword);
    }

    /**
     * @return array<int,RouteInterface>
     */
    public function getRoutes(SubmissionDataSetInterface $submission): array
    {
        $routes = [];
        foreach (array_keys($this->pluginClasses[RouteInterface::class] ?? []) as $keyword) {
            $passCount = $submission->getConfiguration()->getRoutePassCount($keyword);
            for ($pass = 0; $pass < $passCount; $pass++) {
                $routes[] = $this->getRoute($keyword, $submission, $pass);
            }
        }
        return $routes;
    }

    public function getRoute(string $keyword, SubmissionDataSetInterface $submission, int $pass): ?RouteInterface
    {
        return $this->getPlugin($keyword, RouteInterface::class, [$submission, $pass]);
    }

    public function deleteRoute(string $keyword): void
    {
        $this->deletePlugin($keyword, RouteInterface::class);
    }

    public function getRouteDefaultConfigurations(): array
    {
        $result = [];
        foreach ($this->pluginClasses[RouteInterface::class] ?? [] as $key => $class) {
            $result[$key] = $class::getDefaultConfiguration();
        }
        return $result;
    }

    public function getRouteSchema(): SchemaInterface
    {
        return new RouteSchema($this);
    }
}
