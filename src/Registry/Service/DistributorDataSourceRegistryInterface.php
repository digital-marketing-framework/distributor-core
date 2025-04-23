<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceManagerInterface;
use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceStorageInterface;

interface DistributorDataSourceRegistryInterface
{
    public function getDistributorDataSourceManager(): DistributorDataSourceManagerInterface;

    /**
     * @param array<mixed> $additionalArguments
     */
    public function registerDistributorSourceStorage(string $class, array $additionalArguments = [], string $keyword = ''): void;

    /**
     * @return array<DistributorDataSourceStorageInterface>
     */
    public function getAllDistributorSourceStorages(): array;
}
