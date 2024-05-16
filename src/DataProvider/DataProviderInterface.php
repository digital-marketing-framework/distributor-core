<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\Plugin\ConfigurablePluginInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;

interface DataProviderInterface extends ConfigurablePluginInterface
{
    public function enabled(): bool;

    public function addContext(WriteableContextInterface $context): void;

    public function addData(): void;

    public static function getSchema(): SchemaInterface;
}
