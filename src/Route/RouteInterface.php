<?php

namespace DigitalMarketingFramework\Distributor\Core\Route;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Plugin\ConfigurablePluginInterface;

interface RouteInterface extends ConfigurablePluginInterface
{
    public const KEY_ENABLED = 'enabled';

    public const DEFAULT_ENABLED = false;

    public const KEY_GATE = 'gate';

    public const DEFAULT_GATE = [];

    public const KEY_DATA = 'data';

    public function getRouteId(): string;

    public function buildData(): DataInterface;

    public function processGate(): bool;

    public function enabled(): bool;

    public function async(): ?bool;

    public function disableStorage(): ?bool;

    /**
     * @return array<string>
     */
    public function getEnabledDataProviders(): array;

    /**
     * @throws DigitalMarketingFrameworkException
     */
    public function process(): bool;

    public function addContext(ContextInterface $context): void;
}
