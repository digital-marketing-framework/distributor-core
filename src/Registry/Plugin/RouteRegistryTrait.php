<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ListSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\SchemaDocument;
use DigitalMarketingFramework\Core\Plugin\ConfigurablePluginInterface;
use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\Route\RouteSchema;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;

trait RouteRegistryTrait
{
    use PluginRegistryTrait;

    abstract public function getConfigurationSchema(): SchemaDocument;

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
        foreach (array_keys($submission->getConfiguration()->getRoutePasses()) as $index) {
            $routes[] = $this->getRoute($submission, $index);
        }
        return $routes;
    }

    public function getRoute(SubmissionDataSetInterface $submission, int $index): ?RouteInterface
    {
        $routeData = $submission->getConfiguration()->getRoutePassData($index);
        $keyword = $routeData['keyword'];
        return $this->getPlugin($keyword, RouteInterface::class, [$submission, $index]);
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
        $routeSchema = new RouteSchema();
        foreach ($this->pluginClasses[RouteInterface::class] ?? [] as $key => $class) {
            $schema = $class::getSchema();
            $routeSchema->addItem($key, $schema);
        }
        return $routeSchema;
    }

    protected function getRouteListDefaultValue(SchemaDocument $schemaDocument): array
    {
        $defaultValue = [];
        foreach ($this->pluginClasses[RouteInterface::class] ?? [] as $key => $class) {
            $defaultValue[] = [
                'type' => $key,
                'pass' => '',
                'config' => [
                    $key => $schemaDocument->getDefaultValue($class::getSchema()),
                ]
            ];
        }
        return $defaultValue;
    }

    protected function getRoutesSchema(SchemaDocument $schemaDocument): SchemaInterface
    {
        $routeSchema = $this->getRouteSchema();
        $schemaDocument->addCustomType($routeSchema, RouteSchema::TYPE);

        $routeListSchema = new ListSchema(new CustomSchema(RouteSchema::TYPE));
        $routeListDefaultValue = $this->getRouteListDefaultValue($schemaDocument);
        $routeListSchema->setDefaultValue($routeListDefaultValue);

        return $routeListSchema;
    }
}
