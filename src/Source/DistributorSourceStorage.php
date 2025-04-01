<?php

namespace DigitalMarketingFramework\Distributor\Core\Source;

use DigitalMarketingFramework\Core\Model\Source\DistributorSourceInterface;

/**
 * @template SourceClass of DistributorSourceInterface
 * @implements DistributorSourceInterface<SourceClass>
 */
abstract class DistributorSourceStorage implements DistributorSourceStorageInterface
{
    abstract public function getType(): string;
    abstract public function getSourceById(string $id): ?DistributorSourceInterface;
    abstract public function getAllSources(): array;
}
