<?php

namespace DigitalMarketingFramework\Distributor\Core\DataSource;

use DigitalMarketingFramework\Core\DataSource\AbstractApiEndPointDataSourceStorage;
use DigitalMarketingFramework\Core\Model\Api\EndPointInterface;
use DigitalMarketingFramework\Core\Model\DataSource\DataSourceInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSource\ApiEndPointDistributorDataSource;

/**
 * @extends AbstractApiEndPointDataSourceStorage<ApiEndPointDistributorDataSource>
 *
 * @implements DistributorDataSourceStorageInterface<ApiEndPointDistributorDataSource>
 */
class ApiEndPointDistributorDataSourceStorage extends AbstractApiEndPointDataSourceStorage implements DistributorDataSourceStorageInterface
{
    protected function createDataSource(EndPointInterface $endPoint): DataSourceInterface
    {
        return new ApiEndPointDistributorDataSource($endPoint);
    }

    /**
     * Filter for getAllDataSources() and getDataSourceById().
     * Distributor: checks both the general enabled flag and the push enabled flag.
     */
    protected function filterEndPoint(EndPointInterface $endPoint): bool
    {
        return $endPoint->getEnabled() && $endPoint->getPushEnabled();
    }

    /**
     * Filter for getAllDataSourceVariants().
     * Distributor: checks the push enabled flag.
     */
    protected function filterEndPointVariant(EndPointInterface $endPoint): bool
    {
        return $endPoint->getPushEnabled();
    }
}
