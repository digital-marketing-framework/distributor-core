<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataSource;

use DigitalMarketingFramework\Distributor\Core\Model\DataSource\DistributorDataSourceInterface;

class DistributorDataSourceStorageSpy
{
    /**
     * @param array<string,GenericDataSource> $dataSources
     */
    public function __construct(
        protected array $dataSources = [],
    ) {
    }

    public function addDataSource(string $id, string $configurationDocument): void
    {
        $this->dataSources[$id] = new GenericDataSource($configurationDocument);
    }

    public function matches(string $id): bool
    {
        return isset($this->dataSources[$id]);
    }

    /**
     * @param array<string,mixed> $dataSourceContext
     */
    public function getDataSourceById(string $id, array $dataSourceContext): ?DistributorDataSourceInterface
    {
        return $this->dataSources[$id] ?? null;
    }
}
