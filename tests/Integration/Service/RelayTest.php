<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration\Service;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Queue\QueueException;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRoute;
use DigitalMarketingFramework\Distributor\Core\Service\DistributorInterface;
use DigitalMarketingFramework\Distributor\Core\Tests\Integration\DistributorRegistryTestTrait;
use DigitalMarketingFramework\Distributor\Core\Tests\Integration\JobTestTrait;
use DigitalMarketingFramework\Distributor\Core\Tests\Integration\SubmissionTestTrait;
use DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataDispatcher\DataDispatcherSpyInterface;
use DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataDispatcher\SpiedOnDataDispatcher;
use DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataProvider\DataProviderSpyInterface;
use DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataProvider\SpiedOnGenericDataProvider;
use DigitalMarketingFramework\Distributor\Core\Tests\Spy\Route\RouteSpyInterface;
use DigitalMarketingFramework\Distributor\Core\Tests\Spy\Route\SpiedOnGenericRoute;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DigitalMarketingFramework\Distributor\Core\Service\Distributor
 */
class RelayTest extends TestCase
{
    use DistributorRegistryTestTrait;
    use SubmissionTestTrait;
    use JobTestTrait;

    protected RouteSpyInterface&MockObject $routeSpy;

    protected DataProviderSpyInterface&MockObject $dataProviderSpy;

    protected DataDispatcherSpyInterface&MockObject $dataDispatcherSpy;

    protected DistributorInterface $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initRegistry();
        $this->initSubmission();
        $this->subject = $this->registry->getDistributor();
    }

    protected function registerRouteSpy(): RouteSpyInterface&MockObject
    {
        $this->routeSpy = $this->createMock(RouteSpyInterface::class);
        $this->registry->registerOutboundRoute(SpiedOnGenericRoute::class, [$this->routeSpy], 'generic');

        return $this->routeSpy;
    }

    protected function registerDataProviderSpy(): DataProviderSpyInterface&MockObject
    {
        $this->dataProviderSpy = $this->createMock(DataProviderSpyInterface::class);
        $this->registry->registerDataProvider(SpiedOnGenericDataProvider::class, [$this->dataProviderSpy], 'generic');

        return $this->dataProviderSpy;
    }

    protected function registerDataDispatcherSpy(): DataDispatcherSpyInterface&MockObject
    {
        $this->dataDispatcherSpy = $this->createMock(DataDispatcherSpyInterface::class);
        $this->registry->registerDataDispatcher(SpiedOnDataDispatcher::class, [$this->dataDispatcherSpy]);

        return $this->dataDispatcherSpy;
    }

    /**
     * @param array<string,mixed> $configuration
     */
    protected function addRouteSpy(array $configuration, string $routeId, int $weight): RouteSpyInterface&MockObject
    {
        $spy = $this->registerRouteSpy();
        $this->addRouteConfiguration('generic', $routeId, $weight, $configuration);

        return $spy;
    }

    /**
     * @param array<string,mixed> $configuration
     */
    protected function addDataProviderSpy(array $configuration): DataProviderSpyInterface&MockObject
    {
        $spy = $this->registerDataProviderSpy();
        $this->addDataProviderConfiguration('generic', $configuration);

        return $spy;
    }

    /**
     * @param array<mixed> $dataMapperConfig
     *
     * @return array{type:string,config:array{dataMapper:array<mixed>}}
     */
    protected function getDataMapperGroupConfiguration(array $dataMapperConfig): array
    {
        return [
            'type' => 'dataMapper',
            'config' => [
                'dataMapper' => $dataMapperConfig,
            ],
        ];
    }

    /**
     * @return array{type:string,config:array{dataMapper:array{passthroughField:array{enabled:bool}}}}
     */
    protected function getPassthroughDataMapperGroupConfiguration(bool $enabled = true): array
    {
        return $this->getDataMapperGroupConfiguration([
            'data' => [
                'passthroughFields' => [
                    'enabled' => $enabled,
                ],
            ],
        ]);
    }

    protected function configurePassthroughDataMapperGroup(string $dataMapperGroupId): void
    {
        $this->addDataMapperGroupConfiguration($dataMapperGroupId . 'Name', $dataMapperGroupId, 0, $this->getPassthroughDataMapperGroupConfiguration());
    }

    /** @test */
    public function processSyncOneRouteOnePassWithStorage(): void
    {
        $this->setSubmissionAsync(false);
        $this->setStorageEnabled(false);
        $this->configurePassthroughDataMapperGroup('passthroughDataMapperGroupId1');
        $this->addRouteSpy([
            'enabled' => true,
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId1', 10);
        $this->submissionData = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $this->queue->expects($this->once())->method('addJob')->willReturnCallback(static function (JobInterface $job) {
            return $job;
        });
        $this->queue->expects($this->once())->method('markListAsPending');
        $this->queue->expects($this->once())->method('markAsRunning');
        $this->routeSpy->expects($this->once())->method('send')->with([
            'field1' => 'value1',
            'field2' => 'value2',
        ]);
        $this->queue->expects($this->never())->method('markAsFailed');
        $this->queue->expects($this->once())->method('markAsDone');

        $this->temporaryQueue->expects($this->never())->method('addJob');
        $this->temporaryQueue->expects($this->never())->method('markListAsPending');
        $this->temporaryQueue->expects($this->never())->method('markAsRunning');
        $this->temporaryQueue->expects($this->never())->method('markAsFailed');
        $this->temporaryQueue->expects($this->never())->method('markAsDone');

        $this->subject->process($this->getSubmission());
    }

    /** @test */
    public function processSyncOneRouteOnePassWithoutStorage(): void
    {
        $this->setSubmissionAsync(false);
        $this->setStorageEnabled(true);
        $this->configurePassthroughDataMapperGroup('passthroughDataMapperGroupId1');
        $this->addRouteSpy([
            'enabled' => true,
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId1', 10);
        $this->submissionData = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $this->temporaryQueue->expects($this->once())->method('addJob')->willReturnCallback(static function (JobInterface $job) {
            return $job;
        });
        $this->temporaryQueue->expects($this->once())->method('markListAsPending');
        $this->temporaryQueue->expects($this->once())->method('markAsRunning');
        $this->routeSpy->expects($this->once())->method('send')->with([
            'field1' => 'value1',
            'field2' => 'value2',
        ]);
        $this->temporaryQueue->expects($this->never())->method('markAsFailed');
        $this->temporaryQueue->expects($this->once())->method('markAsDone');

        $this->queue->expects($this->never())->method('addJob');
        $this->queue->expects($this->never())->method('markListAsPending');
        $this->queue->expects($this->never())->method('markAsRunning');
        $this->queue->expects($this->never())->method('markAsFailed');
        $this->queue->expects($this->never())->method('markAsDone');

        $this->subject->process($this->getSubmission());
    }

    /** @test */
    public function processAsyncOneRouteOnePassWithStorage(): void
    {
        $this->setSubmissionAsync(true);
        $this->setStorageEnabled(false);
        $this->configurePassthroughDataMapperGroup('passthroughDataMapperGroupId1');
        $this->addRouteSpy([
            'enabled' => true,
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId1', 10);
        $this->submissionData = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $this->queue->expects($this->once())->method('addJob')->willReturnCallback(static function (JobInterface $job) {
            return $job;
        });

        $this->routeSpy->expects($this->never())->method('send');

        $this->queue->expects($this->never())->method('markListAsPending');
        $this->queue->expects($this->never())->method('markAsRunning');
        $this->queue->expects($this->never())->method('markAsFailed');
        $this->queue->expects($this->never())->method('markAsDone');

        $this->temporaryQueue->expects($this->never())->method('addJob');
        $this->temporaryQueue->expects($this->never())->method('markListAsPending');
        $this->temporaryQueue->expects($this->never())->method('markAsRunning');
        $this->temporaryQueue->expects($this->never())->method('markAsFailed');
        $this->temporaryQueue->expects($this->never())->method('markAsDone');

        $this->subject->process($this->getSubmission());
    }

    /**
     * @return array<array{0:bool,1:bool,2:bool,3:bool}>
     */
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
     *
     * @test
     */
    public function processAddContext(bool $async, bool $enableStorage, bool $routeEnabled, bool $dataProviderEnabled): void
    {
        $this->setSubmissionAsync($async);
        $this->setStorageEnabled($enableStorage);
        $this->submissionData = ['field1' => 'value1', 'field2' => 'value2'];
        $this->configurePassthroughDataMapperGroup('passthroughDataMapperGroupId1');
        $this->addRouteSpy([
            'enabled' => $routeEnabled,
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId1', 10);
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
        $this->setStorageEnabled(false);
        $this->configurePassthroughDataMapperGroup('passthroughDataMapperGroupId1');
        $this->addRouteSpy([
            'enabled' => true,
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId1', 10);
        $this->addRouteSpy([
            'enabled' => true,
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId2', 20);
        $this->submissionData = ['field1' => 'value1'];
        $this->queue->expects($this->exactly(2))->method('addJob')->willReturnCallback(static function (JobInterface $job) {
            return $job;
        });
        $this->queue->expects($this->once())->method('markListAsPending');
        $this->queue->expects($this->exactly(2))->method('markAsRunning');
        $this->routeSpy->expects($this->exactly(2))->method('send')->with([
            'field1' => 'value1',
        ]);
        $this->queue->expects($this->never())->method('markAsFailed');
        $this->queue->expects($this->exactly(2))->method('markAsDone');

        $this->subject->process($this->getSubmission());
    }

    /** @test */
    public function processAsyncOneRouteWithMultiplePasses(): void
    {
        $this->setSubmissionAsync(true);
        $this->setStorageEnabled(false);
        $this->configurePassthroughDataMapperGroup('passthroughDataMapperGroupId1');
        $this->addRouteSpy([
            'enabled' => true,
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId1', 10);
        $this->addRouteSpy([
            'enabled' => true,
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId2', 20);
        $this->submissionData = ['field1' => 'value1'];
        $this->queue->expects($this->exactly(2))->method('addJob')->willReturnCallback(static function (JobInterface $job) {
            return $job;
        });
        $this->queue->expects($this->never())->method('markListAsPending');
        $this->queue->expects($this->never())->method('markAsRunning');
        $this->routeSpy->expects($this->never())->method('send');
        $this->queue->expects($this->never())->method('markAsFailed');
        $this->queue->expects($this->never())->method('markAsDone');

        $this->subject->process($this->getSubmission());
    }

    /** @test */
    public function processJobThatSucceeds(): void
    {
        $this->routeSpy = $this->registerRouteSpy();
        $job = $this->createJob(
            [
                'field1' => ['type' => 'string', 'value' => 'value1'],
                'field2' => ['type' => 'string', 'value' => 'value2'],
                'field3' => ['type' => 'string', 'value' => 'value3'],
            ],
            [
                'routeId1' => [
                    'enabled' => true,
                    'data' => 'passthroughDataMapperGroupId1',
                ],
            ],
            [
                'passthroughDataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(),
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

    /**
     * @return array<array{0:string}>
     */
    public function processJobFromSubmissionWithTwoPassesThatBothSucceedProvider(): array
    {
        return [
            'first pass' =>  ['routeId1'],
            'second pass' => ['routeId2'],
        ];
    }

    /**
     * @throws QueueException
     *
     * @dataProvider processJobFromSubmissionWithTwoPassesThatBothSucceedProvider
     *
     * @test
     */
    public function processJobFromSubmissionWithTwoPassesThatBothSucceed(string $routeId): void
    {
        $expectedDataPerRoutePass = [
            'routeId1' => ['field1ext' => 'value2', 'field2ext' => 'value1'],
            'routeId2' => ['field1ext' => 'value2', 'field2ext' => 'value3'],
        ];
        $this->routeSpy = $this->registerRouteSpy();
        $job = $this->createJob(
            [
                'field1' => ['type' => 'string', 'value' => 'value1'],
                'field2' => ['type' => 'string', 'value' => 'value2'],
                'field3' => ['type' => 'string', 'value' => 'value3'],
            ],
            [
                'routeId1' => [
                    'enabled' => true,
                    'data' => 'dataMapperGroupId1',
                ],
                'routeId2' => [
                    'enabled' => true,
                    'data' => 'dataMapperGroupId2',
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getDataMapperGroupConfiguration([
                    'data' => [
                        'fieldMap' => [
                            'enabled' => true,
                            'fields' => [
                                'fieldId1' => $this->createMapItem('field1ext', ['data' => ['type' => 'field', 'config' => ['field' => ['fieldName' => 'field2']]], 'modifiers' => []], 'fieldId1', 10),
                                'fieldId2' => $this->createMapItem('field2ext', ['data' => ['type' => 'field', 'config' => ['field' => ['fieldName' => 'field1']]], 'modifiers' => []], 'fieldId2', 20),
                            ],
                        ],
                    ],
                ]),
                'dataMapperGroupId2' => $this->getDataMapperGroupConfiguration([
                    'data' => [
                        'fieldMap' => [
                            'enabled' => true,
                            'fields' => [
                                'fieldId1' => $this->createMapItem('field1ext', ['data' => ['type' => 'field', 'config' => ['field' => ['fieldName' => 'field2']]], 'modifiers' => []], 'fieldId1', 10),
                                'fieldId2' => $this->createMapItem('field2ext', ['data' => ['type' => 'field', 'config' => ['field' => ['fieldName' => 'field3']]], 'modifiers' => []], 'fieldId2', 20),
                            ],
                        ],
                    ],
                ]),
            ],
            jobRouteId: $routeId
        );
        $this->routeSpy->expects($this->once())->method('send')->with($expectedDataPerRoutePass[$routeId]);
        $result = $this->subject->processJob($job);
        $this->assertTrue($result);
    }

    /** @test */
    public function processJobThatSucceedsButIsSkipped(): void
    {
        $this->routeSpy = $this->registerRouteSpy();
        $job = $this->createJob(
            [
                'field1' => ['type' => 'string', 'value' => 'value1'],
                'field2' => ['type' => 'string', 'value' => 'value2'],
            ],
            [
                'routeId1' => [
                    'enabled' => false,
                    'data' => 'dataMapperGroupId1',
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(),
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
                'field1' => ['type' => 'string', 'value' => 'value1'],
                'field2' => ['type' => 'string', 'value' => 'value2'],
            ],
            [
                'routeId1' => [
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
                    'data' => 'dataMapperGroupId1',
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(),
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
                'field1' => ['type' => 'string', 'value' => 'value1'],
                'field2' => ['type' => 'string', 'value' => 'value2'],
            ],
            [
                'routeId1' => [
                    'enabled' => true,
                    'gate' => [
                        'type' => 'gate',
                        'config' => [
                            'gate' => [
                                'routeId' => 'routeId2',
                            ],
                        ],
                    ],
                    'data' => 'dataMapperGroupId1',
                ],
                'routeId2' => [
                    'enabled' => true,
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(),
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
                'field1' => ['type' => 'string', 'value' => 'value1'],
                'field2' => ['type' => 'string', 'value' => 'value2'],
            ],
            [
                'routeId1' => [
                    'enabled' => true,
                    'data' => 'dataMapperGroupId1',
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(),
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
                'field1' => ['type' => 'string', 'value' => 'value1'],
                'field2' => ['type' => 'string', 'value' => 'value2'],
            ],
            [
                'routeId1' => [
                    'enabled' => true,
                    'data' => 'dataMapperGroupId1',
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(),
            ],
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
                'field1' => ['type' => 'string', 'value' => 'value1'],
                'field2' => ['type' => 'string', 'value' => 'value2'],
            ],
            [
                'routeId1' => [
                    'enabled' => true,
                    'data' => 'dataMapperGroupId1',
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(),
            ],
            [
                'distributor' => [
                    'dataProviders' => [
                        'generic' => ['enabled' => false],
                    ],
                ],
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
                'field1' => ['type' => 'string', 'value' => 'value1'],
                'field2' => ['type' => 'string', 'value' => 'value2'],
            ],
            [
                'routeId1' => [
                    'enabled' => false,
                    'data' => 'dataMapperGroupId1',
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(),
            ],
            [
                'distributor' => [
                    'dataProviders' => [
                        'generic' => ['enabled' => true],
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
            ['field1' => ['type' => 'string', 'value' => 'value1']],
            [
                'routeId1' => [
                    'enabled' => true,
                    'data' => 'dataMapperGroupId1',
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(false),
            ]
        );
        $this->expectException(QueueException::class);
        $this->expectExceptionMessage(sprintf(OutboundRoute::MESSAGE_DATA_EMPTY, 'generic', 'routeId1'));
        $this->subject->processJob($job);
    }
}
