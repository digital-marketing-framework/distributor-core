<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Route;

use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Route\Route;

class GenericRoute extends Route
{
    public function __construct(
        string $keyword, 
        RegistryInterface $registry,
        SubmissionDataSetInterface $submission,
        int $pass,
        protected ?DataDispatcherInterface $dataDispatcher = null,
    ) {
        parent::__construct($keyword, $registry, $submission, $pass);
    }

    protected function getDispatcher(): ?DataDispatcherInterface
    {
        return $this->dataDispatcher;
    }
}
