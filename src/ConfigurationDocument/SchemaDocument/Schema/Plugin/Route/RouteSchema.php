<?php

namespace DigitalMarketingFramework\Distributor\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\Route;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SwitchSchema;

class RouteSchema extends SwitchSchema
{
    public const TYPE = 'ROUTE';

    public function __construct(mixed $defaultValue = null)
    {
        parent::__construct('route', $defaultValue);
        $this->addProperty('pass', new StringSchema());
    }
}
