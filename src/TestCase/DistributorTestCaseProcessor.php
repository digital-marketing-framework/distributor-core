<?php

namespace DigitalMarketingFramework\Distributor\Core\TestCase;

use DigitalMarketingFramework\Core\Model\DataSource\DataSourceInterface;
use DigitalMarketingFramework\Core\Model\Queue\Job;
use DigitalMarketingFramework\Core\TestCase\TestCaseProcessor;
use DigitalMarketingFramework\Core\Registry\RegistryInterface;

use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceManager;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface as DistributorRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Service\DistributorInterface;

class DistributorTestCaseProcessor extends TestCaseProcessor
{
    public const TEST_CASE_TYPE = 'distributor';

    protected DistributorRegistryInterface $distributorRegistry;

    protected DistributorInterface $distributor;

    protected DistributorDataSourceManager $dataSourceManager;

    public function __construct(
        string $keyword,
        protected RegistryInterface $registry,
    ) {
        parent::__construct($keyword);
        $registry = $registry->getRegistryCollection()->getRegistryByClass(DistributorRegistryInterface::class);

        $this->distributor = $registry->getDistributor();
        $this->dataSourceManager = $registry->getDistributorDataSourceManager();
    }

    public function processInput(array $input): array
    {
        $job = new Job(data: $input);
        return $this->distributor->getJobPreviewData($job);
    }

    public function calculateHash(array $input): string
    {
        $job = new Job(data: $input);
        $dataSourceId = $input['submission']['dataSourceId'] ?? '';
        $dataSourceContext = $input['submission']['dataSourceContext'] ?? [];

        if ($dataSourceId === '') {
            return '';
        }

        $dataSource = $this->dataSourceManager->getDataSourceById($dataSourceId, $dataSourceContext);

        if (!$dataSource instanceof DataSourceInterface) {
            return '';
        }

        return $dataSource->getHash();
    }
}
