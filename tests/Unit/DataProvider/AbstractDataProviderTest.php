<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\ContextStackInterface;
use DigitalMarketingFramework\Core\Context\WriteableContext;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\Model\Data\Data;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Tests\ListMapTestTrait;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProviderInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractDataProviderTest extends TestCase
{
    use ListMapTestTrait;

    protected const DATA_PROVIDER_CLASS = '';

    protected const DEFAULT_CONFIG = [
        DataProvider::KEY_ENABLED => DataProvider::DEFAULT_ENABLED,
        DataProvider::KEY_MUST_EXIST => DataProvider::DEFAULT_MUST_EXIST,
        DataProvider::KEY_MUST_BE_EMPTY => DataProvider::DEFAULT_MUST_BE_EMPTY,
    ];

    protected RegistryInterface&MockObject $registry;

    protected ContextInterface&MockObject $globalContext;

    protected SubmissionDataSetInterface&MockObject $submission;

    protected DataInterface $submissionData;

    protected DistributorConfigurationInterface&MockObject $submissionConfiguration;

    protected WriteableContextInterface $submissionContext;

    protected DataProviderInterface $subject;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(RegistryInterface::class);
        $this->globalContext = $this->createMock(ContextStackInterface::class);

        $this->submissionData = new Data();
        $this->submissionConfiguration = $this->createMock(DistributorConfigurationInterface::class);
        $this->submissionContext = new WriteableContext();
        $this->submission = $this->createMock(SubmissionDataSetInterface::class);
        $this->submission->expects($this->any())->method('getData')->willReturn($this->submissionData);
        $this->submission->expects($this->any())->method('getConfiguration')->willReturn($this->submissionConfiguration);
        $this->submission->expects($this->any())->method('getContext')->willReturn($this->submissionContext);
    }

    /**
     * @param array<string,mixed> $config
     */
    protected function setDataProviderConfiguration(array $config, string $keyword = 'myCustomKeyword'): void
    {
        $this->submissionConfiguration->method('getDataProviderConfiguration')->with($keyword)->willReturn($config);
    }

    /**
     * @param array<mixed> $additionalArguments
     * @param ?array<string,mixed> $defaultConfig
     */
    protected function createDataProvider(string $keyword = 'myCustomKeyword', array $additionalArguments = [], ?array $defaultConfig = null): void
    {
        if ($defaultConfig === null) {
            $defaultConfig = static::DEFAULT_CONFIG;
        }

        $class = static::DATA_PROVIDER_CLASS;
        $this->subject = new $class($keyword, $this->registry, $this->submission, ...$additionalArguments);
        $this->subject->setContext($this->globalContext);
        $this->subject->setDefaultConfiguration($defaultConfig);
    }
}
