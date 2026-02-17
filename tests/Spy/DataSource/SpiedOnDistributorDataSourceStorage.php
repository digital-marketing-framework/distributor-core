<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataSource;

use DigitalMarketingFramework\Core\Model\DataSource\DataSourceInterface;
use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceStorage;
use DigitalMarketingFramework\Distributor\Core\Model\DataSource\DistributorDataSourceInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;

/**
 * @extends DistributorDataSourceStorage<DistributorDataSourceInterface>
 */
class SpiedOnDistributorDataSourceStorage extends DistributorDataSourceStorage
{
    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        public DistributorDataSourceStorageSpy $spy,
    ) {
        parent::__construct($keyword, $registry);
    }

    public function getType(): string
    {
        return 'generic';
    }

    public function getAllDataSources(): array
    {
        return [];
    }

    public function matches(string $id): bool
    {
        return $this->spy->matches($id);
    }

    public function getDataSourceByIdentifier(string $identifier): ?DataSourceInterface
    {
        return null;
    }

    public function getDataSourceVariantByIdentifier(string $identifier, bool $maintenanceMode = false): ?DataSourceInterface
    {
        return $this->spy->getDataSourceVariantByIdentifier($identifier);
    }
}
