<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Spy\Route;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Route\Route;

class SpiedOnGenericRoute extends Route
{
    public function __construct(
        string $keyword, 
        RegistryInterface $registry,
        SubmissionDataSetInterface $submission,
        int $pass,
        public RouteSpyInterface $routeSpy,
    ) {
        parent::__construct($keyword, $registry, $submission, $pass);
    }

    public function addContext(ContextInterface $context): void
    {
        $this->routeSpy->addContext($context);
    }

    protected function getDispatcher(): ?DataDispatcherInterface
    {
        return $this->routeSpy;
    }
}
