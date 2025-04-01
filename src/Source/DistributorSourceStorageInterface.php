<?php

namespace DigitalMarketingFramework\Distributor\Core\Source;

use DigitalMarketingFramework\Core\Model\Source\DistributorSourceInterface;

/**
 * @template SourceClass of DistributorSourceInterface
 */
interface DistributorSourceStorageInterface
{
    public function getType(): string;

    /**
     * @return ?SourceClass
     */
    public function getSourceById(string $id): ?DistributorSourceInterface;

    /**
     * @return array<SourceClass>
     */
    public function getAllSources(): array;
}
