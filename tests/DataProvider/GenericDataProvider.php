<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProvider;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;

class GenericDataProvider extends DataProvider
{
    /**
     * @param array<string,mixed> $contextToAdd
     * @param array<string,mixed> $fieldsToAdd
     */
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
