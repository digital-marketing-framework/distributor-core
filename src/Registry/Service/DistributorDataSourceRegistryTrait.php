<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceManager;
use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceManagerInterface;
use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceStorageInterface;

trait DistributorDataSourceRegistryTrait
{
    use PluginRegistryTrait;

    protected ?DistributorDataSourceManagerInterface $distributorDataSourceManager = null;

    public function getDistributorDataSourceManager(): DistributorDataSourceManagerInterface
    {
        if ($this->distributorDataSourceManager === null) {
            $this->distributorDataSourceManager = $this->createObject(DistributorDataSourceManager::class, [$this]);
        }

        return $this->distributorDataSourceManager;
    }

    public function setDistributorDataSourceManager(DistributorDataSourceManagerInterface $distributorDataSourceManager): void
    {
        $this->distributorDataSourceManager = $distributorDataSourceManager;
    }

    public function registerDistributorSourceStorage(string $class, array $additionalArguments = [], string $keyword = ''): void
    {
        $this->registerPlugin(DistributorDataSourceStorageInterface::class, $class, $additionalArguments, $keyword);
    }

    public function getAllDistributorSourceStorages(): array
    {
        return $this->getAllPlugins(DistributorDataSourceStorageInterface::class);
    }
}
