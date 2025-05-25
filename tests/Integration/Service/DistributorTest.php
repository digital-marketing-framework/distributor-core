<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration\Service;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Queue\QueueException;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRoute;
use DigitalMarketingFramework\Distributor\Core\Service\Distributor;
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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Distributor::class)]
class DistributorTest extends TestCase
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
     * @param array<string,mixed> $configuration
     *
     * @return array{type:string,config:array<string,array<string,mixed>>}
     */
    protected function getConditionConfiguration(string $key, array $configuration): array
    {
        return [
            'type' => $key,
            'config' => [
                $key => $configuration,
            ],
        ];
    }

    #[Test]
    public function processSyncOneRouteOnePassWithStorage(): void
    {
        $this->setSubmissionAsync(false);
        $this->setStorageEnabled(true);
        $this->configurePassthroughDataMapperGroup('passthroughDataMapperGroupId1');
        $this->addRouteSpy([
            'enabled' => true,
            'requiredPermission' => 'unregulated:allowed',
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId1', 10);
        $this->submissionData = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $this->queue->expects($this->once())->method('addJob')->willReturnCallback(static fn (JobInterface $job) => $job);
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

    #[Test]
    public function processSyncOneRouteOnePassWithoutStorage(): void
    {
        $this->setSubmissionAsync(false);
        $this->setStorageEnabled(false);
        $this->configurePassthroughDataMapperGroup('passthroughDataMapperGroupId1');
        $this->addRouteSpy([
            'enabled' => true,
            'requiredPermission' => 'unregulated:allowed',
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId1', 10);
        $this->submissionData = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $this->temporaryQueue->expects($this->once())->method('addJob')->willReturnCallback(static fn (JobInterface $job) => $job);
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

    #[Test]
    public function processAsyncOneRouteOnePassWithStorage(): void
    {
        $this->setSubmissionAsync(true);
        $this->setStorageEnabled(true);
        $this->configurePassthroughDataMapperGroup('passthroughDataMapperGroupId1');
        $this->addRouteSpy([
            'enabled' => true,
            'requiredPermission' => 'unregulated:allowed',
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId1', 10);
        $this->submissionData = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $this->queue->expects($this->once())->method('addJob')->willReturnCallback(static fn (JobInterface $job) => $job);

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
    public static function processAddContextProvider(): array
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

    #[Test]
    #[DataProvider('processAddContextProvider')]
    public function processAddContext(bool $async, bool $enableStorage, bool $routeEnabled, bool $dataProviderEnabled): void
    {
        $this->setSubmissionAsync($async);
        $this->setStorageEnabled($enableStorage);
        $this->submissionData = ['field1' => 'value1', 'field2' => 'value2'];
        $this->configurePassthroughDataMapperGroup('passthroughDataMapperGroupId1');
        $this->addRouteSpy([
            'enabled' => $routeEnabled,
            'requiredPermission' => 'unregulated:allowed',
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

    #[Test]
    public function processSyncOneRouteWithMultiplePasses(): void
    {
        $this->setSubmissionAsync(false);
        $this->setStorageEnabled(true);
        $this->configurePassthroughDataMapperGroup('passthroughDataMapperGroupId1');
        $this->addRouteSpy([
            'enabled' => true,
            'requiredPermission' => 'unregulated:allowed',
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId1', 10);
        $this->addRouteSpy([
            'enabled' => true,
            'requiredPermission' => 'unregulated:allowed',
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId2', 20);
        $this->submissionData = ['field1' => 'value1'];
        $this->queue->expects($this->exactly(2))->method('addJob')->willReturnCallback(static fn (JobInterface $job) => $job);
        $this->queue->expects($this->once())->method('markListAsPending');
        $this->queue->expects($this->exactly(2))->method('markAsRunning');
        $this->routeSpy->expects($this->exactly(2))->method('send')->with([
            'field1' => 'value1',
        ]);
        $this->queue->expects($this->never())->method('markAsFailed');
        $this->queue->expects($this->exactly(2))->method('markAsDone');

        $this->subject->process($this->getSubmission());
    }

    #[Test]
    public function processAsyncOneRouteWithMultiplePasses(): void
    {
        $this->setSubmissionAsync(true);
        $this->setStorageEnabled(true);
        $this->configurePassthroughDataMapperGroup('passthroughDataMapperGroupId1');
        $this->addRouteSpy([
            'enabled' => true,
            'requiredPermission' => 'unregulated:allowed',
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId1', 10);
        $this->addRouteSpy([
            'enabled' => true,
            'requiredPermission' => 'unregulated:allowed',
            'data' => 'passthroughDataMapperGroupId1',
        ], 'routeId2', 20);
        $this->submissionData = ['field1' => 'value1'];
        $this->queue->expects($this->exactly(2))->method('addJob')->willReturnCallback(static fn (JobInterface $job) => $job);
        $this->queue->expects($this->never())->method('markListAsPending');
        $this->queue->expects($this->never())->method('markAsRunning');
        $this->routeSpy->expects($this->never())->method('send');
        $this->queue->expects($this->never())->method('markAsFailed');
        $this->queue->expects($this->never())->method('markAsDone');

        $this->subject->process($this->getSubmission());
    }

    #[Test]
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
                    'requiredPermission' => 'unregulated:allowed',
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
    public static function processJobFromSubmissionWithTwoPassesThatBothSucceedProvider(): array
    {
        return [
            'first pass' =>  ['routeId1'],
            'second pass' => ['routeId2'],
        ];
    }

    /**
     * @throws QueueException
     */
    #[Test]
    #[DataProvider('processJobFromSubmissionWithTwoPassesThatBothSucceedProvider')]
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
                    'requiredPermission' => 'unregulated:allowed',
                    'data' => 'dataMapperGroupId1',
                ],
                'routeId2' => [
                    'enabled' => true,
                    'requiredPermission' => 'unregulated:allowed',
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

    #[Test]
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
                    'requiredPermission' => 'unregulated:allowed',
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

    #[Test]
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

    #[Test]
    public function processJobThatSucceedsAndIsNotSkippedBecauseOfAReferencedCondition(): void
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
                    'requiredPermission' => 'unregulated:allowed',
                    'gate' => [
                        'type' => 'reference',
                        'config' => [
                            'reference' => [
                                'conditionId' => 'conditionId1',
                            ],
                        ],
                    ],
                    'data' => 'dataMapperGroupId1',
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(),
            ],
            [
                'conditionId1' => $this->getStaticConditionConfiguration(true),
            ]
        );
        $this->routeSpy->expects($this->once())->method('send')->with([
            'field1' => 'value1',
            'field2' => 'value2',
        ]);
        $result = $this->subject->processJob($job);
        $this->assertTrue($result);
    }

    #[Test]
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
                    'requiredPermission' => 'unregulated:allowed',
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

    #[Test]
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
                    'requiredPermission' => 'unregulated:allowed',
                    'data' => 'dataMapperGroupId1',
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(),
            ],
            config: [
                'dataProcessing' => [
                    'dataProviders' => [
                        'generic' => [
                            'enabled' => true,
                            'requiredPermission' => 'unregulated:allowed',
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

    #[Test]
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
                    'requiredPermission' => 'unregulated:allowed',
                    'data' => 'dataMapperGroupId1',
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(),
            ],
            config: [
                'dataProcessing' => [
                    'dataProviders' => [
                        'generic' => [
                            'enabled' => false,
                            'requiredPermission' => 'unregulated:allowed',
                        ],
                    ],
                ],
            ]
        );
        $this->dataProviderSpy->expects($this->never())->method('process');
        $this->routeSpy->expects($this->once())->method('send')->with(['field1' => 'value1', 'field2' => 'value2']);
        $result = $this->subject->processJob($job);
        $this->assertTrue($result);
    }

    #[Test]
    public function processJobWithDataProviderThatIsNotAllowed(): void
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
                    'requiredPermission' => 'unregulated:allowed',
                    'data' => 'dataMapperGroupId1',
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(),
            ],
            config: [
                'dataProcessing' => [
                    'dataProviders' => [
                        'generic' => [
                            'enabled' => true,
                            'requiredPermission' => 'unregulated:denied',
                        ],
                    ],
                ],
            ]
        );
        $this->dataProviderSpy->expects($this->never())->method('process');
        $this->routeSpy->expects($this->once())->method('send')->with(['field1' => 'value1', 'field2' => 'value2']);
        $result = $this->subject->processJob($job);
        $this->assertTrue($result);
    }

    #[Test]
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
                    'requiredPermission' => 'unregulated:allowed',
                    'data' => 'dataMapperGroupId1',
                ],
            ],
            [
                'dataMapperGroupId1' => $this->getPassthroughDataMapperGroupConfiguration(),
            ],
            config: [
                'dataProcessing' => [
                    'dataProviders' => [
                        'generic' => [
                            'enabled' => true,
                            'requiredPermission' => 'unregulated:allowed',
                        ],
                    ],
                ],
            ]
        );
        $this->dataProviderSpy->expects($this->once())->method('process');
        $this->routeSpy->expects($this->never())->method('send');
        $result = $this->subject->processJob($job);
        $this->assertFalse($result);
    }

    #[Test]
    public function processJobWhichProducesNoDataCausesQueueException(): void
    {
        $this->routeSpy = $this->registerRouteSpy();
        $job = $this->createJob(
            ['field1' => ['type' => 'string', 'value' => 'value1']],
            [
                'routeId1' => [
                    'enabled' => true,
                    'requiredPermission' => 'unregulated:allowed',
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
