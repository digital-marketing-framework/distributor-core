<?php

namespace DigitalMarketingFramework\Distributor\Core\Factory;

use DigitalMarketingFramework\Core\ConfigurationDocument\ConfigurationDocumentManagerInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\Data;
use DigitalMarketingFramework\Core\Model\Queue\Job;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;

class QueueDataFactory implements QueueDataFactoryInterface
{
    const KEY_ROUTE = 'route';
    const DEFAULT_ROUTE = 'undefined';

    const KEY_PASS = 'pass';
    const DEFAULT_PASS = 0;

    const KEY_SUBMISSION = 'submission';
    const DEFAULT_SUBMISSION = [];

    const DEFAULT_LABEL = 'undefined';

    public function __construct(
        protected ConfigurationDocumentManagerInterface $configurationDocumentManager,
    ) {
    }

    protected function createJob(): JobInterface
    {
        return new Job();
    }

    protected function getSubmissionDataHash(array $submissionData): string
    {
        return GeneralUtility::calculateHash($submissionData);
    }

    public function getSubmissionHash(SubmissionDataSetInterface $submission): string
    {
        return $this->getSubmissionDataHash($this->pack($submission));
    }

    public function getJobHash(JobInterface $job): string
    {
        return $this->getSubmissionDataHash($this->getJobSubmissionData($job));
    }

    protected function getSubmissionDataLabel(array $submissionData, string $route, int $pass, string $hash = ''): string
    {
        if (!$hash) {
            $hash = $this->getSubmissionDataHash($submissionData);
        }
        try {
            $submission = $this->unpack($submissionData);
            return $this->getSubmissionLabel($submission, $route, $pass, $hash);
        } catch (DigitalMarketingFrameworkException) {
            return static::DEFAULT_LABEL;
        }
    }

    public function getSubmissionLabel(SubmissionDataSetInterface $submission, string $route, int $pass, string $hash = ''): string
    {
        if (!$hash) {
            $hash = $this->getSubmissionHash($submission);
        }
        return GeneralUtility::shortenHash($hash)
            . '#' . $submission->getConfiguration()->getRoutePassLabel($route, $pass);
    }

    public function getJobLabel(JobInterface $job): string
    {
        return $this->getSubmissionDataLabel(
            $this->getJobSubmissionData($job),
            $this->getJobRoute($job),
            $this->getJobRoutePass($job),
            $job->getHash()
        );
    }

    protected function getJobSubmissionData(JobInterface $job): array
    {
        return $job->getData()[static::KEY_SUBMISSION] ?? static::DEFAULT_SUBMISSION;
    }

    public function getJobRoutePass(JobInterface $job): int
    {
        return $job->getData()[static::KEY_PASS] ?? static::DEFAULT_PASS;
    }

    public function getJobRoute(JobInterface $job): string
    {
        return $job->getData()[static::KEY_ROUTE] ?? static::DEFAULT_ROUTE;
    }

    public function convertSubmissionToJob(SubmissionDataSetInterface $submission, string $route, int $pass, int $status = QueueInterface::STATUS_QUEUED): JobInterface
    {
        $submissionData = $this->pack($submission);
        $job = $this->createJob();
        $job->setStatus($status);
        $job->setData([
            static::KEY_ROUTE => $route,
            static::KEY_PASS => $pass,
            static::KEY_SUBMISSION => $submissionData,
        ]);
        $job->setHash($this->getSubmissionDataHash($submissionData));
        $job->setLabel($this->getSubmissionLabel($submission, $route, $pass, $job->getHash()));
        return $job;
    }

    public function convertJobToSubmission(JobInterface $job): SubmissionDataSetInterface
    {
        return $this->unpack($this->getJobSubmissionData($job));
    }

    protected function pack(SubmissionDataSetInterface $submission): array
    {
        return [
            'data' => $submission->getData()->pack(),
            'configuration' => $submission->getConfiguration()->getRootConfiguration(),
            'context' => $submission->getContext()->toArray(),
        ];
    }

    /**
     * @param array $data
     * @throws DigitalMarketingFrameworkException
     */
    protected function validatePackage(array $data): void
    {
        if (!$data || !is_array($data) || empty($data)) {
            throw new DigitalMarketingFrameworkException('job data is empty');
        }
        if (!isset($data['data']) || !is_array($data['data'])) {
            throw new DigitalMarketingFrameworkException('job has no valid submission data');
        }
        if (!isset($data['configuration']) || !is_array($data['configuration'])) {
            throw new DigitalMarketingFrameworkException('job has no valid submission configuration');
        }
        if (!isset($data['context']) || !is_array($data['context'])) {
            throw new DigitalMarketingFrameworkException('job has no valid submission context');
        }
    }

    protected function unpack(array $data): SubmissionDataSetInterface
    {
        $this->validatePackage($data);

        $submissionData = Data::unpack($data['data']);
        $submissionConfiguration = $this->configurationDocumentManager->getConfigurationStackFromConfiguration($data['configuration']);

        return new SubmissionDataSet(
            $submissionData->toArray(),
            $submissionConfiguration,
            $data['context']
        );
    }

    public function getSubmissionCacheKey(SubmissionDataSetInterface $submission): string
    {
        return serialize($this->pack($submission));
    }
}
