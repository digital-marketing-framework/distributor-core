<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\DataSet;

use DigitalMarketingFramework\Core\Model\DataSet\DataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfigurationInterface;

interface SubmissionDataSetInterface extends DataSetInterface
{
    public function getConfiguration(): SubmissionConfigurationInterface;
}
