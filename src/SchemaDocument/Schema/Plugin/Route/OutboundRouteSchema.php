<?php

namespace DigitalMarketingFramework\Distributor\Core\SchemaDocument\Schema\Plugin\Route;

use DigitalMarketingFramework\Core\SchemaDocument\Condition\EmptyCondition;
use DigitalMarketingFramework\Core\SchemaDocument\Condition\NotCondition;
use DigitalMarketingFramework\Core\SchemaDocument\Condition\OrCondition;
use DigitalMarketingFramework\Core\SchemaDocument\Condition\UniqueCondition;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SwitchSchema;

class OutboundRouteSchema extends SwitchSchema
{
    public const KEY_PASS = 'pass';

    public const TYPE = 'OUTBOUND_ROUTE';

    public function __construct(mixed $defaultValue = null)
    {
        parent::__construct('outboundRoutes', $defaultValue);
        $this->getRenderingDefinition()->setLabel('{type} {pass}');

        $typeValues = $this->typeSchema->getAllowedValues();
        $typeValues->reset();
        $typeValues->addValueSet('outboundRoutes/{../../../..}/all');

        $this->addPassSchema();
    }

    protected function addPassSchema(): void
    {
        $passSchema = new StringSchema();
        // $passSchema->getRenderingDefinition()->addVisibilityCondition(
        //     new OrCondition([
        //         new NotCondition(
        //             new EmptyCondition('.')
        //         ),
        //         new NotCondition(
        //             new UniqueCondition('../' . static::KEY_TYPE, '../../../*/value/' . static::KEY_TYPE)
        //         ),
        //     ])
        // );

        // TODO this condition does not work as intended: uniqueness should only apply to routes of the same type, but it's good enough for now
        $passSchema->addValidation(
            new OrCondition([
                new UniqueCondition('.', '/integrations/*/outboundRoutes/*/value/' . static::KEY_PASS),
                new EmptyCondition('.'),
            ]),
            message: 'Route pass name must be unique',
            strict: true
        );

        $this->addProperty(static::KEY_PASS, $passSchema)->setWeight(1);
    }

    public function addRoute(string $key, SchemaInterface $schema, string $integration, ?string $label = null): void
    {
        $this->addItem($key, $schema, $label);
        $this->addValueToValueSet($this->switchName . '/' . $integration . '/all', $key, $label);
    }
}
