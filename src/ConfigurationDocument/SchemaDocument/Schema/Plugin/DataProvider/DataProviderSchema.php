<?php

namespace DigitalMarketingFramework\Distributor\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\DataProvider;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\PluginSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProviderInterface;

class DataProviderSchema extends PluginSchema
{
    public function addDataProvider(string $keyword, SchemaInterface $schema): void
    {
        $this->addProperty($keyword, $schema);
    }

    protected function getPluginInterface(): string
    {
        return DataProviderInterface::class;
    }

    protected function processPlugin(string $keyword, string $class): void
    {
        $this->addDataProvider($keyword, $class::getSchema());
    }
}
