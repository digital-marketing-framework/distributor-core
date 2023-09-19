<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Route;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Route\Route;

class GenericRoute extends Route
{
    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        protected SubmissionDataSetInterface $submission,
        protected string $routeId,
        protected ?DataDispatcherInterface $dataDispatcher = null,
    ) {
        parent::__construct($keyword, $registry, $submission, $routeId);
    }

    protected function getDispatcher(): DataDispatcherInterface
    {
        if (!$this->dataDispatcher instanceof DataDispatcherInterface) {
            throw new DigitalMarketingFrameworkException('generic route has no data dispatcher');
        }

        return $this->dataDispatcher;
    }
}
