<?php

namespace DigitalMarketingFramework\Distributor\Core\DataSource;

use DigitalMarketingFramework\Core\DataSource\DataSourceStorage;
use DigitalMarketingFramework\Distributor\Core\Model\DataSource\DistributorDataSourceInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;

/**
 * @template DataSourceClass of DistributorDataSourceInterface
 *
 * @extends DataSourceStorage<DataSourceClass>
 *
 * @implements DistributorDataSourceStorageInterface<DataSourceClass>
 */
abstract class DistributorDataSourceStorage extends DataSourceStorage implements DistributorDataSourceStorageInterface
{
    public function __construct(
        string $keyword,
        protected RegistryInterface $registry,
    ) {
        parent::__construct($keyword);
    }
}
