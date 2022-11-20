<?php

namespace DigitalMarketingFramework\Distributer\Core\DataProvider;

use DigitalMarketingFramework\Core\Request\RequestInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;

class RequestVariablesDataProvider extends DataProvider
{
    const KEY_VARIABLE_FIELD_MAP = 'variableFieldMap';
    const DEFAULT_VARIABLE_FIELD_MAP = [];

    protected function processContext(SubmissionDataSetInterface $submission, RequestInterface $request): void
    {
        $variables = array_keys($this->getConfig(static::KEY_VARIABLE_FIELD_MAP));
        foreach ($variables as $variable) {
            $this->addRequestVariableToContext($submission, $request, $variable);
        }
    }

    protected function process(SubmissionDataSetInterface $submission): void
    {
        $variableFieldMap = $this->getConfig(static::KEY_VARIABLE_FIELD_MAP);
        foreach ($variableFieldMap as $variable => $field) {
            $this->setFieldFromRequestVariable($submission, $variable, $field);
        }
    }

    public static function getDefaultConfiguration(): array
    {
        return parent::getDefaultConfiguration() + [
            static::KEY_VARIABLE_FIELD_MAP => static::DEFAULT_VARIABLE_FIELD_MAP,
        ];
    }
}
