<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProvider;

use DigitalMarketingFramework\Core\ConfigurationResolver\Evaluation\EvaluationInterface;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\WriteableContext;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\Model\Data\Data;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProviderInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractDataProviderTest extends TestCase
{
    protected const DATA_PROVIDER_CLASS = '';

    protected RegistryInterface&MockObject $registry;

    protected ContextInterface&MockObject $globalContext;

    protected SubmissionDataSetInterface&MockObject $submission;

    protected DataInterface $submissionData;

    protected SubmissionConfigurationInterface&MockObject $submissionConfiguration;

    protected WriteableContextInterface $submissionContext;

    protected EvaluationInterface&MockObject $enabledEvaluation;

    protected DataProviderInterface $subject;

    public function setUp(): void
    {
        $this->registry = $this->createMock(RegistryInterface::class);
        $this->globalContext = $this->createMock(ContextInterface::class);

        $this->enabledEvaluation = $this->createMock(EvaluationInterface::class);
        $this->registry->expects($this->any())->method('getEvaluation')->willReturn($this->enabledEvaluation);

        $this->submissionData = new Data();
        $this->submissionConfiguration = $this->createMock(SubmissionConfigurationInterface::class);
        $this->submissionContext = new WriteableContext();
        $this->submission = $this->createMock(SubmissionDataSetInterface::class);
        $this->submission->expects($this->any())->method('getData')->willReturn($this->submissionData);
        $this->submission->expects($this->any())->method('getConfiguration')->willReturn($this->submissionConfiguration);
        $this->submission->expects($this->any())->method('getContext')->willReturn($this->submissionContext);
    }

    protected function setDataProviderConfiguration(array $config, string $keyword = 'myCustomKeyword'): void
    {
        $this->submissionConfiguration->method('getDataProviderConfiguration')->with($keyword)->willReturn($config);
    }

    protected function createDataProvider(string $keyword = 'myCustomKeyword', array $additionalArguments = []): void
    {
        $class = static::DATA_PROVIDER_CLASS;
        $this->subject = new $class($keyword, $this->registry, $this->submission, ...$additionalArguments);
    }
}
