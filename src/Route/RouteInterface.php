<?php

namespace DigitalMarketingFramework\Distributor\Core\Route;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Plugin\PluginInterface;

interface RouteInterface extends PluginInterface
{
    public const KEY_ENABLED = 'enabled';
    public const DEFAULT_ENABLED = false;

    public const KEY_GATE = 'gate';
    public const DEFAULT_GATE = [];

    public const KEY_DATA = 'data';

    public function getPass(): int;

    public function enabled(): bool;

    /**
     * @throws DigitalMarketingFrameworkException
     */
    public function process(): bool;

    public function addContext(ContextInterface $context): void;

    public static function getDefaultConfiguration(): array;
}
