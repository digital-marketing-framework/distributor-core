<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ListSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SwitchSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\SchemaDocument;
use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryTrait;
use DigitalMarketingFramework\Core\Utility\ListUtility;
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

    public function getRouteSchema(): SchemaInterface
    {
        $routeSchema = new RouteSchema();
        foreach ($this->getAllPluginClasses(RouteInterface::class) as $key => $class) {
            $schema = $class::getSchema();
            $routeSchema->addItem($key, $schema);
        }
        return $routeSchema;
    }

    protected function getRoutesSchema(SchemaDocument $schemaDocument): SchemaInterface
    {
        $routeSchema = $this->getRouteSchema();
        $schemaDocument->addCustomType($routeSchema, RouteSchema::TYPE);

        $routeListSchema = new ListSchema(new CustomSchema(RouteSchema::TYPE));
        $routeListSchema->setDynamicOrder(true);

        return $routeListSchema;
    }
}
