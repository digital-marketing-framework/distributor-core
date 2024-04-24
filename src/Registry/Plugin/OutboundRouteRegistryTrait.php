<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\SchemaDocument\FieldDefinition\FieldDefinition;
use DigitalMarketingFramework\Core\SchemaDocument\FieldDefinition\FieldListDefinition;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ListSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\SchemaDocument\SchemaDocument;
use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryTrait;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfiguration;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\SchemaDocument\Schema\Plugin\Route\OutboundRouteSchema;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRouteInterface;

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
            $integration = $class::getIntegrationName();
            $integrationLabel = $class::getIntegrationLabel();
            $integrationWeight = $class::getIntegrationWeight();
            $outboundRouteListLabel = $class::getOutboundRouteListLabel();

            $routeSchema->addRoute($key, $schema, $integration, $label);

            $integrationSchema = $this->getIntegrationSchema($schemaDocument, $integration, $integrationLabel, $integrationWeight);
            $routeListSchema = $integrationSchema->getProperty(DistributorConfigurationInterface::KEY_OUTBOUND_ROUTES);
            if (!$routeListSchema instanceof ListSchema) {
                $routeListSchema = new ListSchema(new CustomSchema(OutboundRouteSchema::TYPE));
                if ($outboundRouteListLabel === null) {
                    $outboundRouteListLabel = 'Routes to ' . ($integrationLabel ?? GeneralUtility::getLabelFromValue($integration));
                }
                $routeListSchema->getRenderingDefinition()->setLabel($outboundRouteListLabel);
                $integrationSchema->addProperty(DistributorConfigurationInterface::KEY_OUTBOUND_ROUTES, $routeListSchema);
            }

            $fields = $class::getDefaultFields();
            if ($fields !== []) {
                $fieldListDefinition = new FieldListDefinition(sprintf('distributor.out.defaults.%s.%s', $integration, $key));
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
