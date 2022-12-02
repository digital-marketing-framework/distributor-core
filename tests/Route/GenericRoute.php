<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\Route;

use DigitalMarketingFramework\Distributer\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributer\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributer\Core\Route\Route;

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
