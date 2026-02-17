<?php

namespace DigitalMarketingFramework\Distributor\Core\Factory;

use DigitalMarketingFramework\Core\ConfigurationDocument\ConfigurationDocumentManagerAwareInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\ConfigurationDocumentManagerAwareTrait;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\GlobalConfiguration\GlobalConfigurationAwareInterface;
use DigitalMarketingFramework\Core\GlobalConfiguration\GlobalConfigurationAwareTrait;
use DigitalMarketingFramework\Core\Model\Data\Data;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\SchemaDocument\ConfigurationSchemaAwareInterface;
use DigitalMarketingFramework\Core\SchemaDocument\ConfigurationSchemaAwareTrait;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceManagerAwareInterface;
use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceManagerAwareTrait;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSource\DistributorDataSourceInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Queue\Job;

class QueueDataFactory implements QueueDataFactoryInterface, ConfigurationDocumentManagerAwareInterface, DistributorDataSourceManagerAwareInterface, GlobalConfigurationAwareInterface, ConfigurationSchemaAwareInterface
{
    use ConfigurationDocumentManagerAwareTrait;
    use DistributorDataSourceManagerAwareTrait;
    use GlobalConfigurationAwareTrait;
    use ConfigurationSchemaAwareTrait;

    public const KEY_INTEGRATION_NAME = 'integration';

    public const KEY_ROUTE_ID = 'routeId';

    public const KEY_SUBMISSION = 'submission';

    public const DEFAULT_LABEL = 'undefined';

    /**
     * @param array{data:array<string,array{type:string,value:mixed}>,dataSourceId:string,dataSourceContext?:array<sting,mixed>,context:array<string,mixed>} $submissionData
     */
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

    /**
     * @param array{data:array<string,array{type:string,value:mixed}>,dataSourceId:string,dataSourceContext?:array<string,mixed>,context:array<string,mixed>} $submissionData
     */
    protected function getSubmissionDataLabel(array $submissionData, string $integrationName, string $routeId, string $hash = ''): string
    {
        if ($hash === '') {
            $hash = $this->getSubmissionDataHash($submissionData);
        }

        try {
            $submission = $this->unpack($submissionData);

            return $this->getSubmissionLabel($submission, $integrationName, $routeId, $hash);
        } catch (DigitalMarketingFrameworkException) {
            return static::DEFAULT_LABEL;
        }
    }

    public function getSubmissionLabel(SubmissionDataSetInterface $submission, string $integrationName, string $routeId, string $hash = ''): string
    {
        if ($hash === '') {
            $hash = $this->getSubmissionHash($submission);
        }

        return GeneralUtility::shortenHash($hash)
            . '#' . $submission->getConfiguration()->getOutboundRouteLabel($integrationName, $routeId);
    }

    public function getJobLabel(JobInterface $job): string
    {
        return $this->getSubmissionDataLabel(
            $this->getJobSubmissionData($job),
            $this->getJobRouteIntegrationName($job),
            $this->getJobRouteId($job),
            $job->getHash()
        );
    }

    /**
     * @return array{data:array<string,array{type:string,value:mixed}>,dataSourceId:string,context:array<string,mixed>}
     */
    protected function getJobSubmissionData(JobInterface $job): array
    {
        $jobData = $job->getData();
        if (!isset($jobData[static::KEY_SUBMISSION])) {
            throw new DigitalMarketingFrameworkException('job does not seem to have submission data');
        }

        return $jobData[static::KEY_SUBMISSION];
    }

    public function getJobRouteId(JobInterface $job): string
    {
        $jobData = $job->getData();
        if (!isset($jobData[static::KEY_ROUTE_ID])) {
            throw new DigitalMarketingFrameworkException('job does not seem to have a route id');
        }

        return $jobData[static::KEY_ROUTE_ID];
    }

    public function getJobRouteIntegrationName(JobInterface $job): string
    {
        $jobData = $job->getData();
        if (!isset($jobData[static::KEY_INTEGRATION_NAME])) {
            throw new DigitalMarketingFrameworkException('job does not seem to have an integration name');
        }

        return $jobData[static::KEY_INTEGRATION_NAME];
    }

    public function convertSubmissionToJob(
        SubmissionDataSetInterface $submission,
        string $integrationName,
        string $routeId,
        int $status = QueueInterface::STATUS_QUEUED,
    ): JobInterface {
        $submissionData = $this->pack($submission);
        $job = new Job();
        $job->setSynchronousContext($submission->getContext());
        $job->setStatus($status);
        $job->setData([
            static::KEY_INTEGRATION_NAME => $integrationName,
            static::KEY_ROUTE_ID => $routeId,
            static::KEY_SUBMISSION => $submissionData,
        ]);
        $job->setHash($this->getSubmissionDataHash($submissionData));
        $job->setLabel($this->getSubmissionLabel($submission, $integrationName, $routeId, $job->getHash()));

        return $job;
    }

    public function convertJobToSubmission(JobInterface $job): SubmissionDataSetInterface
    {
        $synchronousContext = null;
        if ($job instanceof Job) {
            $synchronousContext = $job->getSynchronousContext();
        }

        return $this->unpack($this->getJobSubmissionData($job), $synchronousContext);
    }

    /**
     * @return array{data:array<string,array{type:string,value:mixed}>,dataSourceId:string,dataSourceContext?:array<string,mixed>,context:array<string,mixed>}
     */
    protected function pack(SubmissionDataSetInterface $submission): array
    {
        return [
            'data' => $submission->getData()->pack(),
            'dataSourceId' => $submission->getDataSourceIdentifier(),
            'context' => $submission->getContext()->toArray(),
        ];
    }

    /**
     * @param array<mixed> $data
     *
     * @throws DigitalMarketingFrameworkException
     */
    protected function validatePackage(array $data): void
    {
        if ($data === []) {
            throw new DigitalMarketingFrameworkException('job data is empty');
        }

        if (!isset($data['data']) || !is_array($data['data'])) {
            throw new DigitalMarketingFrameworkException('job has no valid submission data');
        }

        if (
            (!isset($data['dataSourceId']) || !is_string($data['dataSourceId']))
            && (!isset($data['configuration']) || !is_array($data['configuration'])) // legacy job support
        ) {
            throw new DigitalMarketingFrameworkException('job has no valid submission data source ID');
        }

        if (!isset($data['context']) || !is_array($data['context'])) {
            throw new DigitalMarketingFrameworkException('job has no valid submission context');
        }
    }

    /**
     * Builds the full data source variant identifier from packed job submission data.
     *
     * Handles three job formats:
     * - Format 1 (legacy): No dataSourceId key, configuration embedded in job. Returns ''.
     * - Format 2 (legacy): dataSourceId + dataSourceContext with pluginId. Appends pluginId.
     * - Format 3 (current): dataSourceId contains the full identifier.
     *
     * @param array<string,mixed> $data The 'submission' part of the job data
     */
    public static function buildDataSourceIdentifier(array $data): string
    {
        if (!isset($data['dataSourceId']) || !is_string($data['dataSourceId'])) {
            return '';
        }

        $identifier = $data['dataSourceId'];
        if (isset($data['dataSourceContext']['pluginId'])) {
            $identifier .= ':' . $data['dataSourceContext']['pluginId'];
        }

        return $identifier;
    }

    /**
     * @param array{dataSourceId:string,dataSourceContext?:array<string,mixed>}|array{configuration:array<string,mixed>} $data
     *
     * @return array<array<string,mixed>>
     */
    protected function unpackConfiguration(array $data): array
    {
        if (isset($data['dataSourceId'])) {
            $identifier = static::buildDataSourceIdentifier($data);
            $dataSource = $this->distributorDataSourceManager->getDataSourceVariantByIdentifier($identifier);
            if (!$dataSource instanceof DistributorDataSourceInterface) {
                throw new DigitalMarketingFrameworkException(sprintf('Distributor data source with identifier "%s" not found', $identifier));
            }

            return $this->configurationDocumentManager->getConfigurationStackFromDocument($dataSource->getConfigurationDocument(), $this->getConfigurationSchemaDocument());
        }

        // legacy job support
        return $this->configurationDocumentManager->getConfigurationStackFromConfiguration($data['configuration'], $this->getConfigurationSchemaDocument());
    }

    /**
     * @param array{data:array<string,array{type:string,value:mixed}>,dataSourceId:string,dataSourceContext?:array<string,mixed>,context:array<string,mixed>}|array{data:array<string,array{type:string,value:mixed}>,configuration:array<string,mixed>,context:array<string,mixed>} $data
     */
    protected function unpack(array $data, ?WriteableContextInterface $synchronousContext = null): SubmissionDataSetInterface
    {
        $this->validatePackage($data);

        $submissionData = Data::unpack($data['data']);
        $submissionConfiguration = $this->unpackConfiguration($data);

        $submission = new SubmissionDataSet(
            static::buildDataSourceIdentifier($data),
            $submissionData->toArray(),
            $submissionConfiguration,
            $synchronousContext ?? $data['context']
        );

        if (!$synchronousContext instanceof WriteableContextInterface) {
            $submission->getContext()->setResponsive(false);
        }

        return $submission;
    }
}
