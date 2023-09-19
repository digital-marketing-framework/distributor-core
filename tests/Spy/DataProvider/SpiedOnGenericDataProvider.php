<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProvider;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;

class SpiedOnGenericDataProvider extends DataProvider
{
    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        SubmissionDataSetInterface $submission,
        public DataProviderSpyInterface $spy
    ) {
        parent::__construct($keyword, $registry, $submission);
    }

    protected function processContext(ContextInterface $context): void
    {
        $this->spy->processContext($context);
    }

    protected function process(): void
    {
        $this->spy->process();
    }
}
