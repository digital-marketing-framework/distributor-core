<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\Spy\Route;

use DigitalMarketingFramework\Core\Request\RequestInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributer\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributer\Core\Route\Route;

class SpiedOnGenericRoute extends Route
{
    public function __construct(
        string $keyword, 
        RegistryInterface $registry,
        public RouteSpyInterface $routeSpy
    ) {
        parent::__construct($keyword, $registry);
    }

    public function addContext(SubmissionDataSetInterface $submission, RequestInterface $request, int $pass): void
    {
        $this->routeSpy->addContext($submission, $request, $pass);
    }

    protected function getDispatcher()
    {
        return $this->routeSpy;
    }
}
