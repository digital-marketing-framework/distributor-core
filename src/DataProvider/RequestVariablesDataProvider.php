<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;

class RequestVariablesDataProvider extends DataProvider
{
    const KEY_VARIABLE_FIELD_MAP = 'variableFieldMap';
    const DEFAULT_VARIABLE_FIELD_MAP = [];

    protected function processContext(ContextInterface $context): void
    {
        $variables = array_keys($this->getConfig(static::KEY_VARIABLE_FIELD_MAP));
        foreach ($variables as $variable) {
            $this->submission->getContext()->copyRequestVariableFromContext($context, $variable);
        }
    }

    protected function process(): void
    {
        $variableFieldMap = $this->getConfig(static::KEY_VARIABLE_FIELD_MAP);
        foreach ($variableFieldMap as $variable => $field) {
            $value = $this->submission->getContext()->getRequestVariable($variable);
            if ($value !== null) {
                $this->setField($field, $value);
            }
        }
    }

    public static function getDefaultConfiguration(): array
    {
        return parent::getDefaultConfiguration() + [
            static::KEY_VARIABLE_FIELD_MAP => static::DEFAULT_VARIABLE_FIELD_MAP,
        ];
    }
}
