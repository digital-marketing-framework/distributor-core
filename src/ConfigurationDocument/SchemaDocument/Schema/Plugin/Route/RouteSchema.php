<?php

namespace DigitalMarketingFramework\Distributor\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\Route;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\PluginSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;

class RouteSchema extends PluginSchema
{
    public function addRoute(string $keyword, SchemaInterface $schema): void
    {
        $this->addProperty($keyword, $schema);
    }

    protected function getPluginInterface(): string
    {
        return RouteInterface::class;
    }

    protected function processPlugin(string $keyword, string $class): void
    {
        $this->addRoute($keyword, $class::getSchema());
    }
}
