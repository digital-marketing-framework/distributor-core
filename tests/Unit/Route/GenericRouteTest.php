<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\Route;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\WriteableContext;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessorInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Log\LoggerInterface;
use DigitalMarketingFramework\Core\Model\Data\Data;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Route\Route;
use DigitalMarketingFramework\Distributor\Core\Tests\Route\GenericRoute;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenericRouteTest extends TestCase
{
    protected const DEFAULT_CONFIG = [
        'enabled' => false,
        'gate' => [],
        'data' => '',
    ];

    protected RegistryInterface&MockObject $registry;

    protected DataProcessorInterface&MockObject $dataProcessor;

    protected ContextInterface&MockObject $globalContext;

    protected LoggerInterface&MockObject $logger;

    protected DataDispatcherInterface&MockObject $dataDispatcher;

    protected SubmissionDataSetInterface&MockObject $submission;

    protected DataInterface $submissionData;

    protected SubmissionConfigurationInterface&MockObject $submissionConfiguration;

    protected WriteableContext $submissionContext;

    protected GenericRoute $subject;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(RegistryInterface::class);
        $this->globalContext = $this->createMock(ContextInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->dataProcessor = $this->createMock(DataProcessorInterface::class);

        $this->dataDispatcher = $this->createMock(DataDispatcherInterface::class);

        $this->submissionData = new Data();
        $this->submissionConfiguration = $this->createMock(SubmissionConfigurationInterface::class);
        $this->submissionContext = new WriteableContext();
        $this->submission = $this->createMock(SubmissionDataSetInterface::class);
        $this->submission->expects($this->any())->method('getData')->willReturn($this->submissionData);
        $this->submission->expects($this->any())->method('getConfiguration')->willReturn($this->submissionConfiguration);
        $this->submission->expects($this->any())->method('getContext')->willReturn($this->submissionContext);
    }

    protected function createRoute(string $keyword = 'myCustomKeyword', string $routeId = 'myCustomKeywordId1', bool $useDispatcher = true, ?SubmissionDataSetInterface $submission = null): void
    {
        $this->subject = new GenericRoute(
            $keyword,
            $this->registry,
            $submission ?? $this->submission,
            $routeId,
            $useDispatcher ? $this->dataDispatcher : null
        );
        $this->subject->setLogger($this->logger);
        $this->subject->setDataProcessor($this->dataProcessor);
        $this->subject->setDefaultConfiguration(static::DEFAULT_CONFIG);
    }

    /** @test */
    public function useCorrectKeyword(): void
    {
        $this->submissionConfiguration->expects($this->once())->method('getRouteConfiguration')->willReturn([
            'enabled' => true,
        ]);
        $this->createRoute();
        $this->assertEquals('myCustomKeyword', $this->subject->getKeyword());
    }

    /** @test */
    public function addContextShouldNotAddAnyContextByDefault(): void
    {
        $this->submissionData['field1'] = 'value1';
        $this->submissionConfiguration->expects($this->once())->method('getRouteConfiguration')->willReturn([
            'enabled' => true,
        ]);

        $this->createRoute();

        $this->subject->addContext($this->globalContext);
        $this->assertEmpty($this->submissionContext->toArray());
    }

    /** @test */
    public function processPassGateFails(): void
    {
        $this->dataProcessor->expects($this->once())->method('processEvaluation')->willReturn(false);
        $this->logger->expects($this->once())->method('debug')->with(sprintf(Route::MESSAGE_GATE_FAILED, 'myCustomKeyword', 'myCustomKeywordId1'));

        $this->submissionConfiguration->expects($this->once())->method('getRouteConfiguration')->willReturn([
            'enabled' => true,
            'gate' => [
                'gateConfigKey1' => 'gateConfigValue1',
            ],
        ]);

        $this->createRoute();
        $result = $this->subject->process();
        $this->assertFalse($result);
    }

    /** @test */
    public function processPassEmptyInputDataWillCauseException(): void
    {
        $this->dataProcessor->expects($this->once())->method('processEvaluation')->willReturn(true);
        $this->submissionConfiguration->expects($this->once())->method('getRouteConfiguration')->willReturn([
            'enabled' => true,
            'gate' => [
                'gateConfigKey1' => 'gateConfigValue1',
            ],
        ]);

        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->expectExceptionMessage(sprintf(Route::MESSGE_NO_STREAM_DEFINED, 'myCustomKeyword', 'myCustomKeywordId1'));

        $this->createRoute();
        $this->subject->process();
    }

    /** @test */
    public function processPassWithFieldConfigWillSendProcessedData(): void
    {
        $this->dataProcessor->expects($this->once())->method('processEvaluation')->willReturn(true);
        $this->submissionData['field1'] = 'value1';
        $this->submissionData['field2'] = 'value2';

        $this->submissionConfiguration->expects($this->once())->method('getRouteConfiguration')->willReturn([
            'enabled' => true,
            'gate' => [
                'gateConfigKey1' => 'gateConfigValue1',
            ],
            'data' => 'streamId1',
        ]);

        $this->submissionConfiguration->expects($this->once())->method('getStreamConfiguration')->with('streamId1')->willReturn([
            'streamConfigKey1' => 'streamConfigValue1',
            'streamConfigKey2' => 'streamConfigValue2',
        ]);

        $this->dataProcessor->expects($this->once())->method('processStream')->willReturn(new Data([
            'processedField1' => 'processedValue1',
            'processedField2' => 'processedValue2',
        ]));

        $this->dataDispatcher->expects($this->once())->method('send')->with([
            'processedField1' => 'processedValue1',
            'processedField2' => 'processedValue2',
        ]);

        $this->createRoute();
        $this->subject->process();
    }
}
