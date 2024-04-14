<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\FieldDefinition\FieldDefinition;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\FieldDefinition\FieldListDefinition;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ListSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\SchemaDocument;
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
     * @return array<RouteInterface>
     */
    public function getRoutes(SubmissionDataSetInterface $submission): array
    {
        $routes = [];
        foreach ($submission->getConfiguration()->getRouteIds() as $routeId) {
            $routes[] = $this->getRoute($submission, $routeId);
        }

        return $routes;
    }

    public function getRoute(SubmissionDataSetInterface $submission, string $routeId): ?RouteInterface
    {
        $keyword = $submission->getConfiguration()->getRouteKeyword($routeId);

        return $this->getPlugin($keyword, RouteInterface::class, [$submission, $routeId]);
    }

    public function deleteRoute(string $keyword): void
    {
        $this->deletePlugin($keyword, RouteInterface::class);
    }

    protected function getRouteSchema(SchemaDocument $schemaDocument): SchemaInterface
    {
        $routeSchema = new RouteSchema();
        foreach ($this->getAllPluginClasses(RouteInterface::class) as $key => $class) {
            $schema = $class::getSchema();
            $label = $class::getLabel();
            $routeSchema->addItem($key, $schema, $label);

            $fields = $class::getDefaultFields();
            if ($fields !== []) {
                $fieldListDefinition = new FieldListDefinition('distributor.out.defaults.' . $key);
                foreach ($fields as $field) {
                    if (!$field instanceof FieldDefinition) {
                        $field = new FieldDefinition($field);
                    }

                    $fieldListDefinition->addField($field);
                }

                $schemaDocument->addFieldContext($fieldListDefinition->getName(), $fieldListDefinition);
            }
        }

        return $routeSchema;
    }

    protected function getRoutesSchema(SchemaDocument $schemaDocument): SchemaInterface
    {
        $routeSchema = $this->getRouteSchema($schemaDocument);
        $schemaDocument->addCustomType($routeSchema, RouteSchema::TYPE);

        $routeListSchema = new ListSchema(new CustomSchema(RouteSchema::TYPE));
        $routeListSchema->setDynamicOrder(true);

        return $routeListSchema;
    }
}
