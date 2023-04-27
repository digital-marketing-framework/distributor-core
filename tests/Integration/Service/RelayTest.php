<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration\Service;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Queue\QueueException;
use DigitalMarketingFramework\Distributor\Core\Route\Route;
use DigitalMarketingFramework\Distributor\Core\Service\Relay;
use DigitalMarketingFramework\Distributor\Core\Tests\Integration\RelayTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers Relay
 */
class RelayTest extends TestCase
{
    use RelayTestTrait;

    protected Relay $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initRelay();
        $this->subject = $this->registry->getRelay();
    }

    /** @test */
    public function processSyncOneRouteOnePassWithStorage() {
        $this->setSubmissionAsync(false);
        $this->setStorageDisabled(false);
        $this->addRouteSpy([
            'enabled' => true,
            'data' => [
                'passthroughFields' => ['enabled' => true]
            ],
        ]);
        $this->submissionData = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $this->queue->expects($this->once())->method('addJob');
        $this->queue->expects($this->once())->method('markListAsPending');
        $this->queue->expects($this->once())->method('markAsRunning');
        $this->routeSpy->expects($this->once())->method('send')->with([
            'field1' => 'value1',
            'field2' => 'value2',
        ]);
        $this->queue->expects($this->once())->method('markAsDone');

        $this->temporaryQueue->expects($this->never())->method('addJob');
        $this->temporaryQueue->expects($this->never())->method('markListAsPending');
        $this->temporaryQueue->expects($this->never())->method('markAsRunning');
        $this->temporaryQueue->expects($this->never())->method('markAsDone');

        $this->subject->process($this->getSubmission());
    }

    /** @test */
    public function processSyncOneRouteOnePassWithoutStorage(): void
    {
        $this->setSubmissionAsync(false);
        $this->setStorageDisabled(true);
        $this->addRouteSpy([
            'enabled' => true,
            'data' => [
                'passthroughFields' => ['enabled' => true],
            ],
        ]);
        $this->submissionData = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $this->temporaryQueue->expects($this->once())->method('addJob');
        $this->temporaryQueue->expects($this->once())->method('markListAsPending');
        $this->temporaryQueue->expects($this->once())->method('markAsRunning');
        $this->routeSpy->expects($this->once())->method('send')->with([
            'field1' => 'value1',
            'field2' => 'value2',
        ]);
        $this->temporaryQueue->expects($this->once())->method('markAsDone');

        $this->queue->expects($this->never())->method('addJob');
        $this->queue->expects($this->never())->method('markListAsPending');
        $this->queue->expects($this->never())->method('markAsRunning');
        $this->queue->expects($this->never())->method('markAsDone');

        $this->subject->process($this->getSubmission());
    }

    /** @test */
    public function processAsyncOneRouteOnePassWithStorage(): void
    {
        $this->setSubmissionAsync(true);
        $this->setStorageDisabled(false);
        $this->addRouteSpy([
            'enabled' => true,
            'data' => [
                'passthroughFields' => ['enabled' => true],
            ],
        ]);
        $this->submissionData = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $this->queue->expects($this->once())->method('addJob');

        $this->routeSpy->expects($this->never())->method('send');

        $this->queue->expects($this->never())->method('markListAsPending');
        $this->queue->expects($this->never())->method('markAsRunning');
        $this->queue->expects($this->never())->method('markAsDone');

        $this->temporaryQueue->expects($this->never())->method('addJob');
        $this->temporaryQueue->expects($this->never())->method('markListAsPending');
        $this->temporaryQueue->expects($this->never())->method('markAsRunning');
        $this->temporaryQueue->expects($this->never())->method('markAsDone');

        $this->subject->process($this->getSubmission());
    }

    public function processAddContextProvider(): array
    {
        return [
            [false, false, false, false],
            [false, false, false, true],
            [false, false, true,  false],
            [false, false, true,  true],
            [false, true,  false, false],
            [false, true,  false, true],
            [false, true,  true,  false],
            [false, true,  true,  true],

            [true,  false, false, false],
            [true,  false, false, true],
            [true,  false, true,  false],
            [true,  false, true,  true],
            [true,  true,  false, false],
            [true,  true,  false, true],
            [true,  true,  true,  false],
            [true,  true,  true,  true],
        ];
    }

    /**
     * @dataProvider processAddContextProvider
     * @test
     */
    public function processAddContext(bool $async, bool $disableStorage, bool $routeEnabled, bool $dataProviderEnabled): void
    {
        $this->setSubmissionAsync($async);
        $this->setStorageDisabled($disableStorage);
        $this->submissionData = ['field1' => 'value1', 'field2' => 'value2'];
        $this->addRouteSpy([
            'enabled' => $routeEnabled,
            'data' => [
                'passthroughFields' => ['enabled' => true],
            ],
        ]);
        $this->addDataProviderSpy([
            'enabled' => $dataProviderEnabled,
        ]);

        // routes always add their context
        $this->routeSpy->expects($this->once())->method('addContext');

        // data providers only add their context if they are enabled
        $this->dataProviderSpy->expects($dataProviderEnabled ? $this->once() : $this->never())->method('processContext');

        $this->subject->process($this->getSubmission());
    }

    /** @test */
    public function processSyncOneRouteWithMultiplePasses(): void
    {
        $this->setSubmissionAsync(false);
        $this->setStorageDisabled(false);
        $this->addRouteSpy([
            'enabled' => true,
            'data' => [
                'passthroughFields' => ['enabled' => true],
            ],
            'passes' => [[], []],
        ]);
        $this->submissionData = [ 'field1' => 'value1', ];
        $this->queue->expects($this->exactly(2))->method('addJob');
        $this->queue->expects($this->once())->method('markListAsPending');
        $this->queue->expects($this->exactly(2))->method('markAsRunning');
        $this->routeSpy->expects($this->exactly(2))->method('send')->with([
            'field1' => 'value1',
        ]);
        $this->queue->expects($this->exactly(2))->method('markAsDone');

        $this->subject->process($this->getSubmission());
    }

    /** @test */
    public function processAsyncOneRouteWithMultiplePasses(): void
    {
        $this->setSubmissionAsync(true);
        $this->setStorageDisabled(false);
        $this->addRouteSpy([
            'enabled' => true,
            'data' => [
                'passthroughFields' => ['enabled' => true],
            ],
            'passes' => [[], []],
        ]);
        $this->submissionData = ['field1' => 'value1'];
        $this->queue->expects($this->exactly(2))->method('addJob');
        $this->queue->expects($this->never())->method('markListAsPending');
        $this->queue->expects($this->never())->method('markAsRunning');
        $this->routeSpy->expects($this->never())->method('send');
        $this->queue->expects($this->never())->method('markAsDone');

        $this->subject->process($this->getSubmission());
    }

    /** @test */
    public function processJobThatSucceeds(): void
    {
        $this->routeSpy = $this->registerRouteSpy();
        $job = $this->createJob(
            [
                'field1' => [ 'type' => 'string', 'value' => 'value1' ],
                'field2' => [ 'type' => 'string', 'value' => 'value2' ],
                'field3' => [ 'type' => 'string', 'value' => 'value3' ],
            ],
            [
                'enabled' => true,
                'data' => [
                    'passthroughFields' => ['enabled' => true],
                ],
            ]
        );
        $this->routeSpy->expects($this->once())->method('send')->with([
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => 'value3',
        ]);
        $result = $this->subject->processJob($job);
        $this->assertTrue($result);
    }

    public function processJobFromSubmissionWithTwoPassesThatBothSucceedsProvider(): array
    {
        return [
            'first pass' =>  [0],
            'second pass' => [1],
        ];
    }

    /**
     * @throws QueueException
     * @dataProvider processJobFromSubmissionWithTwoPassesThatBothSucceedsProvider
     * @test
     */
    public function processJobFromSubmissionWithTwoPassesThatBothSucceed(int $pass): void
    {
        $expectedDataPerRoutePass = [
            0 => ['field1ext' => 'value2', 'field2ext' => 'value1',],
            1 => ['field1ext' => 'value2', 'field2ext' => 'value3',],
        ];
        $this->routeSpy = $this->registerRouteSpy();
        $job = $this->createJob(
            [
                'field1' => [ 'type' => 'string', 'value' => 'value1' ],
                'field2' => [ 'type' => 'string', 'value' => 'value2' ],
                'field3' => [ 'type' => 'string', 'value' => 'value3' ],
            ],
            [
                'enabled' => true,
                'data' => [
                    'fields' => [
                        'enabled' => true,
                        'fields' => [
                            'field1ext' => ['data' => [ 'type' => 'field', 'config' => [ 'field' => [ 'fieldName' => 'field1' ] ] ], 'modifiers' => []],
                            'field2ext' => ['data' => [ 'type' => 'field', 'config' => [ 'field' => [ 'fieldName' => 'field2' ] ] ], 'modifiers' => []],
                        ],
                    ],
                ],
                'passes' => [
                    [
                        'data' => [
                            'fields' => [
                                'enabled' => true,
                                'fields' => [
                                    'field1ext' => ['data' => [ 'type' => 'field', 'config' => [ 'field' => [ 'fieldName' => 'field2' ] ] ], 'modifiers' => []],
                                    'field2ext' => ['data' => [ 'type' => 'field', 'config' => [ 'field' => [ 'fieldName' => 'field1' ] ] ], 'modifiers' => []],
                                ],
                            ],
                        ],
                    ],
                    [
                        'data' => [
                            'fields' => [
                                'enabled' => true,
                                'fields' => [
                                    'field1ext' => ['data' => [ 'type' => 'field', 'config' => [ 'field' => [ 'fieldName' => 'field2' ] ] ], 'modifiers' => []],
                                    'field2ext' => ['data' => [ 'type' => 'field', 'config' => [ 'field' => [ 'fieldName' => 'field3' ] ] ], 'modifiers' => []],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $pass
        );
        $this->routeSpy->expects($this->once())->method('send')->with($expectedDataPerRoutePass[$pass]);
        $result = $this->subject->processJob($job);
        $this->assertTrue($result);
    }

    /** @test */
    public function processJobThatSucceedsButIsSkipped(): void
    {
        $this->routeSpy = $this->registerRouteSpy();
        $job = $this->createJob(
            [
                'field1' => [ 'type' => 'string', 'value' => 'value1' ],
                'field2' => [ 'type' => 'string', 'value' => 'value2' ],
            ],
            [
                'enabled' => false,
                'data' => [
                    'passthroughFields' => ['enabled' => true],
                ],
            ]
        );
        $this->routeSpy->expects($this->never())->method('send');
        $result = $this->subject->processJob($job);
        $this->assertFalse($result);
    }

    /** @test */
    public function processJobThatSucceedsButIsSkippedBecauseOfItsGate(): void
    {
        $this->routeSpy = $this->registerRouteSpy();
        $job = $this->createJob(
            [
                'field1' => [ 'type' => 'string', 'value' => 'value1' ],
                'field2' => [ 'type' => 'string', 'value' => 'value2' ],
            ],
            [
                'enabled' => true,
                'gate' => [
                    'type' => 'comparison',
                    'config' => [
                        'comparison' => [
                            'type' => 'equals',
                            'firstOperand' => ['data' => ['type' => 'field', 'config' => ['field' => ['fieldName' => 'field1']]], 'modifiers' => []],
                            'secondOperand' => ['data' => ['type' => 'constant', 'config' => ['constant' => ['value' => 'value2']]], 'modifiers' => []],
                        ],
                    ],
                ],
                'data' => [
                    'passthroughFields' => ['enabled' => true],
                ],
            ]
        );
        $this->routeSpy->expects($this->never())->method('send');
        $result = $this->subject->processJob($job);
        $this->assertFalse($result);
    }

    /** @test */
    public function processJobThatSucceedsAndIsNotSkippedBecauseOfAForeignGate(): void
    {
        $this->routeSpy = $this->registerRouteSpy();
        $job = $this->createJob(
            [
                'field1' => [ 'type' => 'string', 'value' => 'value1' ],
                'field2' => [ 'type' => 'string', 'value' => 'value2' ],
            ],
            [
                'enabled' => true,
                'gate' => [
                    'type' => 'gate',
                    'config' => [
                        'gate' => [
                            'key' => 'route2',
                        ]
                    ],
                ],
                'data' => [
                    'passthroughFields' => ['enabled' => true],
                ],
            ],
            0,
            [
                'distributor' => [
                    'routes' => [
                        'route2' => [
                            'enabled' => true,
                        ],
                    ],
                ],
            ]
        );
        $this->routeSpy->expects($this->once())->method('send')->with([
            'field1' => 'value1',
            'field2' => 'value2',
        ]);
        $result = $this->subject->processJob($job);
        $this->assertTrue($result);
    }

    /** @test */
    public function processJobThatFails(): void
    {
        $errorMessage = 'my error message';
        $this->routeSpy = $this->registerRouteSpy();
        $job = $this->createJob(
            [
                'field1' => [ 'type' => 'string', 'value' => 'value1' ],
                'field2' => [ 'type' => 'string', 'value' => 'value2' ],
            ],
            [
                'enabled' => true,
                'data' => [
                    'passthroughFields' => ['enabled' => true],
                ],
            ]
        );
        $this->routeSpy->expects($this->once())->method('send')->willThrowException(new DigitalMarketingFrameworkException($errorMessage));
        $this->expectException(QueueException::class);
        $this->expectExceptionMessage($errorMessage);
        $this->subject->processJob($job);
    }

    /** @test */
    public function processJobWithDataProviderThatIsEnabled(): void
    {
        $this->routeSpy = $this->registerRouteSpy();
        $this->dataProviderSpy = $this->registerDataProviderSpy();
        $job = $this->createJob(
            [
                'field1' => [ 'type' => 'string', 'value' => 'value1' ],
                'field2' => [ 'type' => 'string', 'value' => 'value2' ],
            ],
            [
                'enabled' => true,
                'data' => [
                    'passthroughFields' => ['enabled' => true],
                ],
            ],
            0,
            [
                'distributor' => [
                    'dataProviders' => [
                        'generic' => [
                            'enabled' => true,
                        ],
                    ],
                ],
            ]
        );
        $this->dataProviderSpy->expects($this->once())->method('process');
        $this->routeSpy->expects($this->once())->method('send')->with(['field1' => 'value1', 'field2' => 'value2']);
        $result = $this->subject->processJob($job);
        $this->assertTrue($result);
    }

    /** @test */
    public function processJobWithDataProviderThatIsNotEnabled(): void
    {
        $this->routeSpy = $this->registerRouteSpy();
        $this->dataProviderSpy = $this->registerDataProviderSpy();
        $job = $this->createJob(
            [
                'field1' => [ 'type' => 'string', 'value' => 'value1' ],
                'field2' => [ 'type' => 'string', 'value' => 'value2' ],
            ],
            [
                'enabled' => true,
                'data' => [
                    'passthroughFields' => ['enabled' => true],
                ],
            ],
            0,
            [
                [
                    'distributor' => [
                        'dataProviders' => [
                            'generic' => [ 'enabled' => false, ]
                        ],
                    ],
                ]
            ]
        );
        $this->dataProviderSpy->expects($this->never())->method('process');
        $this->routeSpy->expects($this->once())->method('send')->with(['field1' => 'value1', 'field2' => 'value2']);
        $result = $this->subject->processJob($job);
        $this->assertTrue($result);
    }

    /** @test */
    public function processJobWithDataProviderThatIsEnabledButRouteIsDisabled(): void
    {
        $this->routeSpy = $this->registerRouteSpy();
        $this->dataProviderSpy = $this->registerDataProviderSpy();
        $job = $this->createJob(
            [
                'field1' => [ 'type' => 'string', 'value' => 'value1' ],
                'field2' => [ 'type' => 'string', 'value' => 'value2' ],
            ],
            [
                'enabled' => false,
                'data' => [
                    'passthroughFields' => ['enabled' => true],
                ],
            ],
            0,
            [
                'distributor' => [
                    'dataProviders' => [
                        'generic' => [ 'enabled' => true, ]
                    ],
                ],
            ]
        );
        $this->dataProviderSpy->expects($this->once())->method('process');
        $this->routeSpy->expects($this->never())->method('send');
        $result = $this->subject->processJob($job);
        $this->assertFalse($result);
    }

    /** @test */
    public function processJobWhichProducesNoDataCausesQueueException(): void
    {
        $this->routeSpy = $this->registerRouteSpy();
        $job = $this->createJob(
            [ 'field1' => [ 'type' => 'string', 'value' => 'value1' ], ],
            [
                'enabled' => true,
                'data' => [
                    'passthroughFields' => ['enabled' => false],
                ],
            ]
        );
        $this->expectException(QueueException::class);
        $this->expectExceptionMessage(sprintf(Route::MESSAGE_DATA_EMPTY, 'generic', 0));
        $this->subject->processJob($job);
    }
}
