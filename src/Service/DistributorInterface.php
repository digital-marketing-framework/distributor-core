<?php

namespace DigitalMarketingFramework\Distributor\Core\Service;

use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Queue\WorkerInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;

interface DistributorInterface extends WorkerInterface
{
    /**
     * @return array<JobInterface>
     */
    public function process(SubmissionDataSetInterface $submission): array;

    /**
     * @return array<string,mixed>
     */
    public function getPreviewData(JobInterface $job): array;
}
