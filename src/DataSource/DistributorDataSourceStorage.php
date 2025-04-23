<?php

namespace DigitalMarketingFramework\Distributor\Core\DataSource;

use DigitalMarketingFramework\Core\DataSource\DataSourceStorage;
use DigitalMarketingFramework\Distributor\Core\Model\DataSource\DistributorDataSourceInterface;

/**
 * @template DataSourceClass of DistributorDataSourceInterface
 * @extends DataSourceStorage<DataSourceClass>
 * @implements DistributorDataSourceInterface<DataSourceClass>
 */
abstract class DistributorDataSourceStorage extends DataSourceStorage implements DistributorDataSourceStorageInterface
{
}
