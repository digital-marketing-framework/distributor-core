<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceManagerInterface;
use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceStorageInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSource\DistributorDataSourceInterface;

interface DistributorDataSourceRegistryInterface
{
    public function getDistributorDataSourceManager(): DistributorDataSourceManagerInterface;

    public function setDistributorDataSourceManager(DistributorDataSourceManagerInterface $distributorDataSourceManager): void;

    /**
     * @param array<mixed> $additionalArguments
     */
    public function registerDistributorSourceStorage(string $class, array $additionalArguments = [], string $keyword = ''): void;

    /**
     * @return array<DistributorDataSourceStorageInterface<DistributorDataSourceInterface>>
     */
    public function getAllDistributorSourceStorages(): array;
}
