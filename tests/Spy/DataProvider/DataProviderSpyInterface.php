<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\Spy\DataProvider;

use DigitalMarketingFramework\Core\Request\RequestInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;

interface DataProviderSpyInterface
{
    public function processContext(SubmissionDataSetInterface $submission, RequestInterface $request): void;
    public function process(SubmissionDataSetInterface $submission): void;
}
