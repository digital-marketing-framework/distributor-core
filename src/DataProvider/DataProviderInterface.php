<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Plugin\ConfigurablePluginInterface;

interface DataProviderInterface extends ConfigurablePluginInterface
{
    public function enabled(): bool;
    public function addContext(ContextInterface $context): void;
    public function addData(): void;

    public static function getSchema(): SchemaInterface;
}
