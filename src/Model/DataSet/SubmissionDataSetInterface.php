<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\DataSet;

use DigitalMarketingFramework\Core\Model\DataSet\DataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;

interface SubmissionDataSetInterface extends DataSetInterface
{
    public function getConfiguration(): DistributorConfigurationInterface;
}
