<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\Route;

use DigitalMarketingFramework\Core\ConfigurationResolver\ContentResolver\GeneralContentResolver;
use DigitalMarketingFramework\Core\ConfigurationResolver\Evaluation\EvaluationInterface;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Log\LoggerInterface;
use DigitalMarketingFramework\Core\Model\Data\Data;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Route\Route;
use DigitalMarketingFramework\Distributor\Core\Tests\Route\GenericRoute;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenericRouteTest extends TestCase
{
    protected RegistryInterface&MockObject $registry;

    protected ContextInterface&MockObject $globalContext;

    protected LoggerInterface&MockObject $logger;

    protected DataDispatcherInterface&MockObject $dataDispatcher;

    protected SubmissionDataSetInterface&MockObject $submission;

    protected DataInterface $submissionData;

    protected SubmissionConfigurationInterface&MockObject $submissionConfiguration;

    protected WriteableContextInterface&MockObject $submissionContext;

    protected EvaluationInterface&MockObject $gateEvaluation;

    protected GeneralContentResolver&MockObject $contentResolver;

    protected GenericRoute $subject;

    public function setUp(): void
    {
        $this->registry = $this->createMock(RegistryInterface::class);
        $this->globalContext = $this->createMock(ContextInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->gateEvaluation = $this->createMock(EvaluationInterface::class);
        $this->registry->expects($this->any())->method('getEvaluation')->willReturn($this->gateEvaluation);

        $this->contentResolver = $this->createMOck(GeneralContentResolver::class);
        $this->registry->expects($this->any())->method('getContentResolver')->willReturn($this->contentResolver);

        $this->dataDispatcher = $this->createMock(DataDispatcherInterface::class);

        $this->submissionData = new Data();
        $this->submissionConfiguration = $this->createMock(SubmissionConfigurationInterface::class);
        $this->submissionContext = $this->createMock(WriteableContextInterface::class);
        $this->submission = $this->createMock(SubmissionDataSetInterface::class);
        $this->submission->expects($this->any())->method('getData')->willReturn($this->submissionData);
        $this->submission->expects($this->any())->method('getConfiguration')->willReturn($this->submissionConfiguration);
        $this->submission->expects($this->any())->method('getContext')->willReturn($this->submissionContext);
    }

    protected function createRoute(string $keyword = 'myCustomKeyword', bool $useDispatcher = true, ?SubmissionDataSetInterface $submission = null): void
    {
        $this->subject = new GenericRoute(
            $keyword,
            $this->registry,
            $submission ?? $this->submission,
            0,
            $useDispatcher ? $this->dataDispatcher : null
        );
        $this->subject->setLogger($this->logger);
    }

    /** @test */
    public function useCorrectKeyword(): void
    {
        $this->createRoute();
        $this->assertEquals('myCustomKeyword', $this->subject->getKeyword());
    }

    /** @test */
    public function addContextShouldNotAddAnyContextByDefault(): void
    {
        $submission = new SubmissionDataSet(['field1' => 'value1']);

        $this->createRoute(submission:$submission);
        $this->subject->addContext($this->globalContext);
        $this->assertEmpty($submission->getContext()->toArray());
    }

    /** @test */
    public function processPassGateFails(): void
    {
        $this->gateEvaluation->expects($this->once())->method('eval')->willReturn(false);
        $this->logger->expects($this->once())->method('debug')->with(sprintf(Route::MESSAGE_GATE_FAILED, 'myCustomKeyword', 0));

        $this->submissionConfiguration->expects($this->once())->method('getRoutePassConfiguration')->willReturn([
            'enabled' => true,
        ]);

        $this->createRoute();
        $result = $this->subject->process();
        $this->assertFalse($result);
    }

    /** @test */
    public function processPassEmptyInputDataWillCauseException(): void
    {
        $this->gateEvaluation->expects($this->once())->method('eval')->willReturn(true);
        $this->submissionConfiguration->expects($this->once())->method('getRoutePassConfiguration')->willReturn([
            'enabled' => true,
        ]);

        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->expectExceptionMessage(sprintf(Route::MESSAGE_DATA_EMPTY, 'myCustomKeyword', 0));

        $this->createRoute();
        $this->subject->process();
    }

    /** @test */
    public function processPassNoDispatcherWillCauseException(): void
    {
        $this->gateEvaluation->expects($this->once())->method('eval')->willReturn(true);
        $this->submissionData['field1'] = 'value1';

        $this->submissionConfiguration->expects($this->once())->method('getRoutePassConfiguration')->willReturn([
            'enabled' => true,
            'fields' => [
                'field1' => ['field' => 'field1'],
            ],
        ]);

        $this->contentResolver->expects($this->once())->method('resolve')->willReturn(
            new Data([
                'field1' => 'processedValue1',
            ])
        );

        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->expectExceptionMessage(sprintf(Route::MESSAGE_DISPATCHER_NOT_FOUND, 'myCustomKeyword', 0));

        $this->createRoute(useDispatcher:false);
        $this->subject->process();
    }

    /** @test */
    public function processPassWithFieldConfigWillSendProcessedData(): void
    {
        $this->gateEvaluation->expects($this->once())->method('eval')->willReturn(true);
        $this->submissionData['field1'] = 'value1';
        $this->submissionData['field2'] = 'value2';

        $this->submissionConfiguration->expects($this->once())->method('getRoutePassConfiguration')->willReturn([
            'enabled' => true,
            'fields' => [
                'processedField1' => 'someConfig',
                'processedField2' => 'someOtherConfig',
            ],
        ]);

        $this->contentResolver->expects($this->once())->method('resolve')->willReturn(new Data([
            'processedField1' => 'processedValue1',
            'processedField2' => 'processedValue2',
        ]));

        $this->dataDispatcher->expects($this->once())->method('send')->with([
            'processedField1' => 'processedValue1',
            'processedField2' => 'processedValue2'
        ]);

        $this->createRoute();
        $this->subject->process();
    }
}
