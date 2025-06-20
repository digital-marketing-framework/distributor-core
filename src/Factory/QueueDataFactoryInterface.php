<?php

namespace DigitalMarketingFramework\Distributor\Core\Factory;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;

interface QueueDataFactoryInterface
{
    /**
     * Returns a hash built over the form data and the context of the submission.
     */
    public function getSubmissionHash(SubmissionDataSetInterface $submission): string;

    /**
     * Returns a hash built over the form data and the context of the submission.
     * Does not contain the route name or the route pass.
     */
    public function getJobHash(JobInterface $job): string;

    /**
     * Returns the label of a set of a submission, a route name and a route pass to a label used in jobs.
     */
    public function getSubmissionLabel(SubmissionDataSetInterface $submission, string $integrationName, string $routeId, string $hash = ''): string;

    /**
     * Returns the label of the job.
     */
    public function getJobLabel(JobInterface $job): string;

    /**
     * Converts a set of a submission and a route ID to a job.
     * Additionally the initial status of the job can be passed too.
     */
    public function convertSubmissionToJob(SubmissionDataSetInterface $submission, string $integrationName, string $routeId, int $status = QueueInterface::STATUS_QUEUED): JobInterface;

    /**
     * Converts a job to a submission.
     *
     * @throws DigitalMarketingFrameworkException
     */
    public function convertJobToSubmission(JobInterface $job): SubmissionDataSetInterface;

    /**
     * Returns the route ID of a job.
     */
    public function getJobRouteId(JobInterface $job): string;

    /**
     * Returns the integration name of a job.
     */
    public function getJobRouteIntegrationName(JobInterface $job): string;
}
