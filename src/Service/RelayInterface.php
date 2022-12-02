<?php

namespace DigitalMarketingFramework\Distributor\Core\Service;

use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;

interface RelayInterface
{
    public function process(SubmissionDataSetInterface $submission): void;
}
