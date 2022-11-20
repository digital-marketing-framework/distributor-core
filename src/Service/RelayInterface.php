<?php

namespace DigitalMarketingFramework\Distributor\Core\Service;

use DigitalMarketingFramework\Core\Queue\WorkerInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;

interface RelayInterface extends WorkerInterface
{
    public function process(SubmissionDataSetInterface $submission): void;
}
