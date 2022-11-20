<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\Spy\DataProvider;

use DigitalMarketingFramework\Core\Request\RequestInterface;
use DigitalMarketingFramework\Distributer\Core\DataProvider\DataProvider;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributer\Core\Registry\RegistryInterface;

class SpiedOnGenericDataProvider extends DataProvider
{
    public function __construct(
        string $keyword, 
        RegistryInterface $registry, 
        public DataProviderSpyInterface $spy
    ) {
        parent::__construct($keyword, $registry);
    }

    protected function processContext(SubmissionDataSetInterface $submission, RequestInterface $request): void
    {
        $this->spy->processContext($submission, $request);
    }

    protected function process(SubmissionDataSetInterface $submission): void
    {
        $this->spy->process($submission);
    }
}
