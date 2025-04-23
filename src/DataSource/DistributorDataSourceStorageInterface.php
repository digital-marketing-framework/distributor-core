<?php

namespace DigitalMarketingFramework\Distributor\Core\DataSource;

use DigitalMarketingFramework\Core\DataSource\DataSourceStorageInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSource\DistributorDataSourceInterface;

/**
 * @template DataSourceClass of DistributorDataSourceInterface
 * @extends DataSourceStorageInterface<DataSourceClass>
 */
interface DistributorDataSourceStorageInterface extends DataSourceStorageInterface
{
}
