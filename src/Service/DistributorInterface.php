<?php

namespace DigitalMarketingFramework\Distributor\Core\Service;

use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Queue\WorkerInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;

interface DistributorInterface extends WorkerInterface
{
    public function process(SubmissionDataSetInterface $submission): void;

    public function previewJobProcess(JobInterface $job): string;
}
