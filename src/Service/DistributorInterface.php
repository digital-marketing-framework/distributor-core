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

    public function previewJobProcess(JobInterface $job): string;

    /**
     * @return array<mixed>
     */
    public function getJobPreviewData(JobInterface $job): array;
}
