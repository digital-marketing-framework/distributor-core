<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Plugin\PluginInterface;

interface DataProviderInterface extends PluginInterface
{
    public function enabled(): bool;
    public function addContext(ContextInterface $context): void;
    public function addData(): void;

    public static function getDefaultConfiguration(): array;
}
