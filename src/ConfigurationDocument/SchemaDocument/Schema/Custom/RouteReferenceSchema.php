<?php

namespace DigitalMarketingFramework\Distributor\Core\ConfigurationDocument\SchemaDocument\Schema\Custom;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\RenderingDefinition\RenderingDefinitionInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\StringSchema;

class RouteReferenceSchema extends StringSchema
{
    public function __construct(
        mixed $defaultValue = null,
        bool $required = true
    ) {
        parent::__construct($defaultValue);
        if (!$required) {
            $this->allowedValues->addValue('', 'Select Route');
        }
        $this->allowedValues->addReference('/distributor/routes/*', label:'{value/type} {value/pass}');
        $this->getRenderingDefinition()->setFormat(RenderingDefinitionInterface::FORMAT_SELECT);
    }
}
