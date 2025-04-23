<?php

namespace DigitalMarketingFramework\Distributor\Core\DataSource;

trait DistributorDataSourceManagerAwareTrait
{
    protected DistributorDataSourceManagerInterface $distributorDataSourceManager;

    public function setDistributorDataSourceManager(DistributorDataSourceManagerInterface $distributorDataSourceManager): void
    {
        $this->distributorDataSourceManager = $distributorDataSourceManager;
    }
}
