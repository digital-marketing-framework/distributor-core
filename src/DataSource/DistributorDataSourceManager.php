<?php

namespace DigitalMarketingFramework\Distributor\Core\DataSource;

use DigitalMarketingFramework\Core\DataSource\DataSourceManager;
use DigitalMarketingFramework\Distributor\Core\Model\DataSource\DistributorDataSourceInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;

/**
 * @extends DataSourceManager<DistributorDataSourceInterface>
 */
class DistributorDataSourceManager extends DataSourceManager implements DistributorDataSourceManagerInterface
{
    /**
     * @var ?array<DistributorDataSourceStorageInterface<DistributorDataSourceInterface>>
     */
    protected ?array $sourceStorages = null;

    public function __construct(
        protected RegistryInterface $registry,
    ) {
    }

    protected function getDataSourceStorages(): array
    {
        if ($this->sourceStorages === null) {
            $this->sourceStorages = $this->registry->getAllDistributorSourceStorages();
        }

        return $this->sourceStorages;
    }
}
