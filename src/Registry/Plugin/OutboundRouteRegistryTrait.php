<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryTrait;
use DigitalMarketingFramework\Core\SchemaDocument\FieldDefinition\FieldDefinition;
use DigitalMarketingFramework\Core\SchemaDocument\FieldDefinition\FieldListDefinition;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ListSchema;
use DigitalMarketingFramework\Core\SchemaDocument\SchemaDocument;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRouteInterface;
use DigitalMarketingFramework\Distributor\Core\SchemaDocument\RenderingDefinition\Icon;
use DigitalMarketingFramework\Distributor\Core\SchemaDocument\Schema\Plugin\Route\OutboundRouteSchema;

trait OutboundRouteRegistryTrait
{
    use PluginRegistryTrait;

    public function registerOutboundRoute(string $class, array $additionalArguments = [], string $keyword = ''): void
    {
        $this->registerPlugin(OutboundRouteInterface::class, $class, $additionalArguments, $keyword);
    }

    /**
     * @return array<OutboundRouteInterface>
     */
    public function getOutboundRoutes(SubmissionDataSetInterface $submission): array
    {
        $routes = [];
        foreach ($submission->getConfiguration()->getOutboundRouteIds() as $integrationName => $routeIds) {
            foreach ($routeIds as $routeId) {
                $routes[] = $this->getOutboundRoute($submission, $integrationName, $routeId);
            }
        }

        return $routes;
    }

    public function getOutboundRoute(SubmissionDataSetInterface $submission, string $integrationName, string $routeId): ?OutboundRouteInterface
    {
        $keyword = $submission->getConfiguration()->getOutboundRouteKeyword($integrationName, $routeId);

        return $this->getPlugin($keyword, OutboundRouteInterface::class, [$submission, $routeId]);
    }

    public function deleteOutboundRoute(string $keyword): void
    {
        $this->deletePlugin($keyword, OutboundRouteInterface::class);
    }

    protected function addOutboundRouteSchema(SchemaDocument $schemaDocument): void
    {
        $routeSchema = new OutboundRouteSchema();
        foreach ($this->getAllPluginClasses(OutboundRouteInterface::class) as $key => $class) {
            $schema = $class::getSchema();
            $label = $class::getLabel();
            $integrationInfo = $class::getDefaultIntegrationInfo();

            $routeSchema->addRoute($key, $schema, $integrationInfo->getName(), $label);

            $integrationSchema = $this->getIntegrationSchemaForPlugin($schemaDocument, $integrationInfo);
            $integrationInfo->addSchema($schemaDocument, $integrationSchema);

            $routeListSchema = $integrationSchema->getProperty(DistributorConfigurationInterface::KEY_OUTBOUND_ROUTES)?->getSchema();
            if (!$routeListSchema instanceof ListSchema) {
                $routeListSchema = new ListSchema(new CustomSchema(OutboundRouteSchema::TYPE));

                $routeListSchema->getRenderingDefinition()->setLabel($integrationInfo->getOutboundRouteListLabel());
                $routeListSchema->getRenderingDefinition()->setIcon(Icon::OUTBOUND_ROUTES);
                $integrationSchema->addProperty(DistributorConfigurationInterface::KEY_OUTBOUND_ROUTES, $routeListSchema);
            }

            $fields = $class::getDefaultFields();
            if ($fields !== []) {
                $fieldListDefinition = new FieldListDefinition(sprintf('distributor.out.defaults.%s.%s', $integrationInfo->getName(), $key));
                foreach ($fields as $field) {
                    if (!$field instanceof FieldDefinition) {
                        $field = new FieldDefinition($field);
                    }

                    $fieldListDefinition->addField($field);
                }

                $schemaDocument->addFieldContext($fieldListDefinition->getName(), $fieldListDefinition);
            }
        }

        $schemaDocument->addCustomType($routeSchema, OutboundRouteSchema::TYPE);
    }
}
