<?php

namespace DigitalMarketingFramework\Distributor\Core\DataSource;

interface DistributorDataSourceManagerAwareInterface
{
    public function setDistributorDataSourceManager(DistributorDataSourceManagerInterface $distributorDataSourceManager): void;
}
