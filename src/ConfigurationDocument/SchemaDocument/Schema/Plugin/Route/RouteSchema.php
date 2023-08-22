<?php

namespace DigitalMarketingFramework\Distributor\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\Route;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Condition\EmptyCondition;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Condition\NotCondition;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Condition\OrCondition;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Condition\UniqueCondition;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SwitchSchema;

class RouteSchema extends SwitchSchema
{
    public const KEY_PASS = 'pass';

    public const TYPE = 'ROUTE';

    public function __construct(mixed $defaultValue = null)
    {
        parent::__construct('route', $defaultValue);
        $this->getRenderingDefinition()->setLabel('{type} {pass}');

        $passSchema = new StringSchema();
        $passSchema->getRenderingDefinition()->addVisibilityCondition(new NotCondition(new UniqueCondition('../' . static::KEY_TYPE, '../../../*/value/' . static::KEY_TYPE)));

        // TODO this condition does not work as intended: uniqueness should only apply to routes of the same type, but it's good enough for now
        $passSchema->addValidation(
            new OrCondition([
                new UniqueCondition('.', '../../../*/value/' . static::KEY_PASS),
                new EmptyCondition('.')
            ]),
            message:'Route pass name must be unique',
            strict:true
        );

        $this->addProperty(static::KEY_PASS, $passSchema);
    }
}
