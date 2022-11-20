<?php

namespace DigitalMarketingFramework\Distributor\Core\Factory;

use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;

interface QueueDataFactoryInterface
{
    /**
     * Returns a hash built over the form data and the context of the submission.
     * Does not contain submission configuration.
     */
    public function getSubmissionHash(SubmissionDataSetInterface $submission): string;

    /**
     * Returns a hash built over the form data and the context of the submission.
     * Does not contain submission configuration or the route name or the route pass.
     */
    public function getJobHash(JobInterface $job): string;

    /**
     * Returns the label of a set of a submission, a route name and a route pass to a label used in jobs.
     */
    public function getSubmissionLabel(SubmissionDataSetInterface $submission, string $route, int $pass, string $hash = ''): string;

    /**
     * Returns the label of the job.
     */
    public function getJobLabel(JobInterface $job): string;

    /**
     * Converts a set of a submission, a route name and a route pass to a job.
     * Additionally the initial status of the job can be passed too.
     */
    public function convertSubmissionToJob(SubmissionDataSetInterface $submission, string $route, int $pass, int $status = QueueInterface::STATUS_PENDING): JobInterface;

    /**
     * Converts a job to a submission.
     */
    public function convertJobToSubmission(JobInterface $job): SubmissionDataSetInterface;

    /**
     * Returns the route pass of a job.
     */
    public function getJobRoutePass(JobInterface $job): int;

    /**
     * Returns the route name of a job.
     */
    public function getJobRoute(JobInterface $job): string;

    /**
     * Returns a cache key for the submission built over the form data, context and configuration of the submission.
     * Unlike the hash it does contain the submission configuration.
     * It is used to cache actions made on submissions (like processing of data providers).
     */
    public function getSubmissionCacheKey(SubmissionDataSetInterface $submission): string;
}
