<?php

namespace DigitalMarketingFramework\Distributer\Core\Model\DataSet;

use DigitalMarketingFramework\Core\Model\DataSet\DataSetInterface;
use DigitalMarketingFramework\Distributer\Core\Model\Configuration\SubmissionConfigurationInterface;

interface SubmissionDataSetInterface extends DataSetInterface
{
    public function getConfiguration(): SubmissionConfigurationInterface;
}
