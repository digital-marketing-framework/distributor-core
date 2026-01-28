<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\ContextStackInterface;
use DigitalMarketingFramework\Core\Context\WriteableContext;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\DataPrivacy\DataPrivacyManagerInterface;
use DigitalMarketingFramework\Core\GlobalConfiguration\GlobalConfigurationAwareInterface;
use DigitalMarketingFramework\Core\GlobalConfiguration\GlobalConfigurationInterface;
use DigitalMarketingFramework\Core\GlobalConfiguration\Settings\CoreSettings;
use DigitalMarketingFramework\Core\Model\Data\Data;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Model\Data\Value\DateTimeValue;
use DigitalMarketingFramework\Core\Tests\ListMapTestTrait;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProvider;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class DataProviderTestBase extends TestCase
{
    use ListMapTestTrait;

    protected const DATA_PROVIDER_CLASS = '';

    protected const DEFAULT_CONFIG = [
        DataProvider::KEY_ENABLED => DataProvider::DEFAULT_ENABLED,
        DataProvider::KEY_REQUIRED_PERMISSION => 'unregulated:allowed', // TODO implement tests for required permissions
        DataProvider::KEY_MUST_EXIST => DataProvider::DEFAULT_MUST_EXIST,
        DataProvider::KEY_MUST_BE_EMPTY => DataProvider::DEFAULT_MUST_BE_EMPTY,
    ];

    protected RegistryInterface&MockObject $registry;

    protected ContextInterface&MockObject $globalContext;

    protected SubmissionDataSetInterface&MockObject $submission;

    protected DataPrivacyManagerInterface&MockObject $dataPrivacyManager;

    protected GlobalConfigurationInterface&MockObject $globalConfiguration;

    protected CoreSettings&MockObject $coreSettings;

    protected DataInterface $submissionData;

    protected DistributorConfigurationInterface&MockObject $submissionConfiguration;

    protected WriteableContextInterface $submissionContext;

    protected DataProvider $subject;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(RegistryInterface::class);
        $this->globalContext = $this->createMock(ContextStackInterface::class);
        $this->dataPrivacyManager = $this->createMock(DataPrivacyManagerInterface::class);
        $this->dataPrivacyManager->expects($this->any())->method('getPermission')->willReturnMap([['unregulated:allowed', true], ['unregulated:denied', false]]);

        $this->coreSettings = $this->createMock(CoreSettings::class);
        $this->coreSettings->method('getDefaultTimezone')->willReturn(DateTimeValue::DEFAULT_TIMEZONE);

        $this->globalConfiguration = $this->createMock(GlobalConfigurationInterface::class);
        $this->globalConfiguration->method('getGlobalSettings')->with(CoreSettings::class)->willReturn($this->coreSettings);

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

    protected function injectGlobalConfiguration(object $subject): void
    {
        if ($subject instanceof GlobalConfigurationAwareInterface) {
            $subject->setGlobalConfiguration($this->globalConfiguration);
        }
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
        $this->subject->setDataPrivacyManager($this->dataPrivacyManager);
        $this->subject->setContext($this->globalContext);
        $this->subject->setDefaultConfiguration($defaultConfig);
        $this->injectGlobalConfiguration($this->subject);
    }
}
