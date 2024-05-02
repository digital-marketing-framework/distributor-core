<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\MapSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\StringSchema;

class RequestVariablesDataProvider extends DataProvider
{
    public const KEY_VARIABLE_FIELD_MAP = 'variableFieldMap';

    protected function processContext(ContextInterface $context): void
    {
        $variables = array_keys($this->getMapConfig(static::KEY_VARIABLE_FIELD_MAP));
        foreach ($variables as $variable) {
            $this->submission->getContext()->copyRequestVariableFromContext($context, $variable);
        }
    }

    protected function process(): void
    {
        $variableFieldMap = $this->getMapConfig(static::KEY_VARIABLE_FIELD_MAP);
        foreach ($variableFieldMap as $variable => $field) {
            $value = $this->submission->getContext()->getRequestVariable($variable);
            if ($value !== null) {
                $this->setField($field, $value);
            }
        }
    }

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema $schema */
        $schema = parent::getSchema();
        $variableMapSchema = new MapSchema(new StringSchema('fieldName'), new StringSchema('variableName'));
        $variableMapSchema->getRenderingDefinition()->setNavigationItem(false);
        $schema->addProperty(static::KEY_VARIABLE_FIELD_MAP, $variableMapSchema);

        return $schema;
    }
}
