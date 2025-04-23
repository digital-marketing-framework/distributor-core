<?php

namespace DigitalMarketingFramework\Distributor\Core\Route;

use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Route\RouteInterface;
use DigitalMarketingFramework\Core\SchemaDocument\FieldDefinition\FieldDefinition;

interface OutboundRouteInterface extends RouteInterface
{
    public const KEY_ENABLED = 'enabled';

    public const DEFAULT_ENABLED = false;

    public const KEY_REQUIRED_PERMISSION = 'requiredPermission';

    public const KEY_GATE = 'gate';

    public const KEY_DATA = 'data';

    public function getRouteId(): string;

    public function buildData(): DataInterface;

    public function processGate(): bool;

    public function enabled(): bool;

    public function allowed(): bool;

    public function async(): ?bool;

    public function enableStorage(): ?bool;

    /**
     * @return array<string>
     */
    public function getEnabledDataProviders(): array;

    public static function getDefaultPassthroughFields(): bool;

    /**
     * @return array<string|FieldDefinition>
     */
    public static function getDefaultFields(): array;

    /**
     * @throws DigitalMarketingFrameworkException
     */
    public function process(): bool;

    /**
     * @return array<string,mixed>
     */
    public function getPreviewData(bool $renderDispatcherPreview = false): array;

    /**
     * @throws DigitalMarketingFrameworkException
     */
    public function preview(): string;

    public function addContext(WriteableContextInterface $context): void;

    public function canRetryOnFail(): bool;
}
