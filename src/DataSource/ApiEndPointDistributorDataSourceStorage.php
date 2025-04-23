<?php

namespace DigitalMarketingFramework\Distributor\Core\DataSource;

use DigitalMarketingFramework\Core\Api\EndPoint\EndPointStorageAwareInterface;
use DigitalMarketingFramework\Core\Api\EndPoint\EndPointStorageAwareTrait;
use DigitalMarketingFramework\Core\Api\EndPoint\EndPointStorageInterface;
use DigitalMarketingFramework\Core\Model\Api\EndPointInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSource\ApiEndPointDistributorDataSource;
use DigitalMarketingFramework\Distributor\Core\Model\DataSource\DistributorDataSourceInterface;

/**
 * @extends DistributorDataSourceStorage<ApiEndPointDistributorDataSource>
 */
class ApiEndPointDistributorDataSourceStorage extends DistributorDataSourceStorage implements EndPointStorageAwareInterface
{
    use EndPointStorageAwareTrait;

    public function getType(): string
    {
        return ApiEndPointDistributorDataSource::TYPE;
    }

    protected function updateEndPoint(?EndPointInterface $endPoint): ?EndPointInterface
    {
        if (!$endPoint instanceof EndPointInterface) {
            return null;
        }

        if (!$endPoint->getEnabled() || !$endPoint->getPushEnabled()) {
            return null;
        }

        return $endPoint;
    }

    public function getDataSourceById(string $id): ?DistributorDataSourceInterface
    {
        if (!$this->matches($id)) {
            return null;
        }

        $name = $this->getInnerIdentifier($id);
        $endPoint = $this->endPointStorage->getEndPointByName($name);
        $endPoint = $this->updateEndPoint($endPoint);

        if ($endPoint instanceof EndPointInterface) {
            return new ApiEndPointDistributorDataSource($endPoint);
        }

        return null;
    }

    public function getAllDataSources(): array
    {
        $result = [];
        foreach ($this->endPointStorage->getAllEndPoints() as $endPoint) {
            $endPoints = $this->updateEndPoint($endPoint);
            if ($endPoint instanceof EndPointInterface) {
                $result[] = new ApiEndPointDistributorDataSource($endPoint);
            }
        }

        return $result;
    }
}
