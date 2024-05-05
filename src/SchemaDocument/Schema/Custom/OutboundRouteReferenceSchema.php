<?php

namespace DigitalMarketingFramework\Distributor\Core\SchemaDocument\Schema\Custom;

use DigitalMarketingFramework\Core\Model\Configuration\ConfigurationInterface;
use DigitalMarketingFramework\Core\SchemaDocument\RenderingDefinition\RenderingDefinitionInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\IntegrationReferenceSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;

class OutboundRouteReferenceSchema extends IntegrationReferenceSchema
{
    public const KEY_ROUTE_REFERENCE = 'routeReference';

    protected StringSchema $routeReferenceSchema;

    public function __construct(
        protected int $integrationNestingLevel = -1,
        mixed $defaultValue = null,
        bool $required = true
    ) {
        parent::__construct($defaultValue, $required);

        $this->routeReferenceSchema = new StringSchema();
        if ($required) {
            $this->routeReferenceSchema->setRequired();
        }

        $this->routeReferenceSchema->getAllowedValues()->addValue('', 'Please select');
        $this->routeReferenceSchema->getAllowedValues()->addReference(
            sprintf('/%s/{../%s}/%s/*', ConfigurationInterface::KEY_INTEGRATIONS, static::KEY_INTEGRATION_REFERENCE, DistributorConfigurationInterface::KEY_OUTBOUND_ROUTES),
            ignorePath: $this->getIntegrationIgnorePath(),
            label: '{value/type} {value/pass}'
        );
        $this->routeReferenceSchema->getRenderingDefinition()->setFormat(RenderingDefinitionInterface::FORMAT_SELECT);
        $this->routeReferenceSchema->getRenderingDefinition()->setLabel('Route');
        $this->addProperty(static::KEY_ROUTE_REFERENCE, $this->routeReferenceSchema);

        $this->getRenderingDefinition()->setLabel('{integrationReference} / {routeReference}');
    }

    protected function getIntegrationIgnorePath(): array
    {
        $ignorePath = parent::getIntegrationIgnorePath();
        if ($this->integrationNestingLevel === 0) {
            $ignorePath[] = sprintf('/%s/{.}', ConfigurationInterface::KEY_INTEGRATIONS);
        } elseif ($this->integrationNestingLevel > 0) {
            $pathParts = array_fill(0, $this->integrationNestingLevel, '..');
            $path = implode('/', $pathParts);
            $ignorePath[] = sprintf('/%s/{%s}', ConfigurationInterface::KEY_INTEGRATIONS, $path);
        }

        return $ignorePath;
    }

    /**
     * @param array{routeReference?:string} $config
     */
    public static function getOutboundRouteId(array $config): string
    {
        return $config[static::KEY_ROUTE_REFERENCE] ?? '';
    }
}
