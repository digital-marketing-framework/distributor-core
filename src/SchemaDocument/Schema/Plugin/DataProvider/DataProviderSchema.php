<?php

namespace DigitalMarketingFramework\Distributor\Core\SchemaDocument\Schema\Plugin\DataProvider;

use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;

class DataProviderSchema extends ContainerSchema
{
    public const VALUE_SET_DATA_PROVIDER_KEYWORDS = 'dataProvider/all';

    public function addItem(string $keyword, SchemaInterface $schema, ?string $label = null): void
    {
        $this->addValueToValueSet(static::VALUE_SET_DATA_PROVIDER_KEYWORDS, $keyword, $label);
        $this->addProperty($keyword, $schema);
    }
}
