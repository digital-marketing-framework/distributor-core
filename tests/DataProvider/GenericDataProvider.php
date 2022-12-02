<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Distributer\Core\DataProvider\DataProvider;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributer\Core\Registry\RegistryInterface;

class GenericDataProvider extends DataProvider
{
    public function __construct(
        string $keyword, 
        RegistryInterface $registry,
        SubmissionDataSetInterface $submission,
        protected array $contextToAdd = [],
        protected array $fieldsToAdd = [],
    ) {
        parent::__construct($keyword, $registry, $submission);
    }

    protected function processContext(ContextInterface $context): void
    {
        foreach ($this->contextToAdd as $key => $value) {
            $this->submission->getContext()[$key] = $value;
        }
    }

    protected function process(): void
    {
        foreach ($this->fieldsToAdd as $field => $value) {
            $this->setField($field, $value);
        }
    }
}
