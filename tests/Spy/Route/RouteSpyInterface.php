<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\Spy\Route;

use DigitalMarketingFramework\Core\Request\RequestInterface;
use DigitalMarketingFramework\Distributer\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;

interface RouteSpyInterface extends DataDispatcherInterface
{
    public function addContext(SubmissionDataSetInterface $submission, RequestInterface $request, int $pass);
}
