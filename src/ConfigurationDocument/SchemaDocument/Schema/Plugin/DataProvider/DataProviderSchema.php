<?php

namespace DigitalMarketingFramework\Distributor\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\DataProvider;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;

class DataProviderSchema extends ContainerSchema
{
    public const VALUE_SET_ROUTE_KEYWORDS = 'dataProvider/all';

    public function addItem(string $keyword, SchemaInterface $schema, ?string $label = null): void
    {
        $this->addValueToValueSet(static::VALUE_SET_ROUTE_KEYWORDS, $keyword, $label);
        $this->addProperty($keyword, $schema);
    }
}
