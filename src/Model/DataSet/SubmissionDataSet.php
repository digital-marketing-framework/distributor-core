<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\DataSet;

use DigitalMarketingFramework\Core\Context\WriteableContext;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\Model\Data\Data;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfiguration;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;

class SubmissionDataSet implements SubmissionDataSetInterface
{
    protected DataInterface $data;

    protected DistributorConfigurationInterface $configuration;

    protected WriteableContextInterface $context;

    /**
     * @param array<string,string|ValueInterface>|DataInterface $data The form fields and their values as associative array
     * @param array<int,array<string,mixed>>|DistributorConfigurationInterface $configurationList An array of (override) configurations
     * @param array<string,mixed>|WriteableContextInterface $context The context needed for processing the submission
     */
    public function __construct(
        array|DataInterface $data,
        array|DistributorConfigurationInterface $configurationList = [],
        array|WriteableContextInterface $context = []
    ) {
        $this->data = $data instanceof DataInterface
            ? $data
            : new Data($data);

        $this->configuration = $configurationList instanceof DistributorConfigurationInterface
            ? $configurationList
            : new DistributorConfiguration($configurationList);

        $this->context = $context instanceof WriteableContextInterface
            ? $context
            : new WriteableContext($context);
    }

    public function getData(): DataInterface
    {
        return $this->data;
    }

    public function getConfiguration(): DistributorConfigurationInterface
    {
        return $this->configuration;
    }

    public function getContext(): WriteableContextInterface
    {
        return $this->context;
    }
}
