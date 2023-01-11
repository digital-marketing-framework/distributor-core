<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\Service;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Log\LoggerInterface;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Queue\QueueProcessorInterface;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactoryInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;
use DigitalMarketingFramework\Distributor\Core\Service\Relay;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RelayTest extends TestCase
{
    protected RegistryInterface&MockObject $registry;

    protected LoggerInterface&MockObject $logger;

    protected ContextInterface&MockObject $context;

    protected QueueInterface&MockObject $persistentQueue;

    protected QueueInterface&MockObject $temporaryQueue;

    protected QueueDataFactoryInterface&MockObject $queueDataFactory;

    protected QueueProcessorInterface&MockObject $persistentQueueProcessor;

    protected QueueProcessorInterface&MockObject $temporaryQueueProcessor;

    /** @var array<RouteInterface&MockObject> */
    protected array $routes = [];

    /** @var array<JobInterface&MockObject> */
    protected array $jobs = [];

    /** @var array<mixed> */
    protected array $routeConfigs = [];

    protected SubmissionDataSetInterface&MockObject $submission;

    protected SubmissionConfigurationInterface&MockObject $submissionConfiguration;

    protected Relay $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->persistentQueue = $this->createMock(QueueInterface::class);
        $this->temporaryQueue = $this->createMock(QueueInterface::class);
        $this->queueDataFactory = $this->createMock(QueueDataFactoryInterface::class);
        $this->persistentQueueProcessor = $this->createMock(QueueProcessorInterface::class);
        $this->temporaryQueueProcessor = $this->createMock(QueueProcessorInterface::class);

        $this->registry = $this->createMock(RegistryInterface::class);
        $this->registry->method('getContext')->willReturn($this->context);
        $this->registry->method('getPersistentQueue')->willReturn($this->persistentQueue);
        $this->registry->method('getNonPersistentQueue')->willReturn($this->temporaryQueue);
        $this->registry->method('getQueueDataFactory')->willReturn($this->queueDataFactory);

        $this->registry->method('getRoutes')->willReturnCallback(function() {
            return $this->routes;
        });

        $this->subject = new Relay($this->registry);
        $this->subject->setLogger($this->logger);
        $this->subject->setContext($this->context);

        $this->registry->method('getQueueProcessor')->willReturnMap([
            [$this->persistentQueue, $this->subject, $this->persistentQueueProcessor],
            [$this->temporaryQueue, $this->subject, $this->temporaryQueueProcessor],
        ]);
    }

    protected function initSubmission(): void
    {
        $this->submissionConfiguration = $this->createMock(SubmissionConfigurationInterface::class);
        $this->submissionConfiguration->method('getWithRoutePassOverride')->willReturnCallback(function($name, $route, $pass, $default) {
            return $this->routeConfigs[$route][$pass][$name];
        });

        $this->submission = $this->createMock(SubmissionDataSetInterface::class);
        $this->submission->method('getConfiguration')->willReturn($this->submissionConfiguration);
    }

    protected function addRoute(string $keyword, array $passes, bool $enabled = true): void
    {
        $this->routeConfigs[$keyword] = $passes;
        foreach (array_keys($passes) as $pass) {
            $route = $this->createMock(RouteInterface::class);
            $route->method('getKeyword')->willReturn($keyword);
            $route->method('getPass')->willReturn($pass);
            $route->method('enabled')->willReturn($enabled);
            $this->routes[] = $route;

            $job = $this->createMock(JobInterface::class);
            $this->jobs[$keyword . ':' . $pass] = $job;
        }
    }

    /** @test */
    public function processSyncOneRouteOnePassWithStorage(): void
    {
        $this->initSubmission();
        $this->addRoute('route1', [
            ['async' => false, 'disableStorage' => false]
        ]);

        $this->logger->expects($this->never())->method('error');

        $this->queueDataFactory
            ->expects($this->exactly(1))
            ->method('convertSubmissionToJob')
            ->withConsecutive(
                [$this->submission, 'route1', 0, QueueInterface::STATUS_PENDING]
            )
            ->willReturnOnConsecutiveCalls(...array_values($this->jobs));

        $this->persistentQueue
            ->expects($this->exactly(1))
            ->method('addJob')
            ->withConsecutive(
                [$this->jobs['route1:0']]
            );

        $this->temporaryQueue
            ->expects($this->never())
            ->method('addJob');

        $this->persistentQueueProcessor
            ->expects($this->once())
            ->method('processJobs')
            ->with([
                $this->jobs['route1:0'],
            ]);

        $this->temporaryQueueProcessor
            ->expects($this->never())
            ->method('processJobs');

        $this->subject->process($this->submission);
    }

    /** @test */
    public function processSyncOneRouteOnePassWithoutStorage(): void
    {
        $this->initSubmission();
        $this->addRoute('route1', [
            ['async' => false, 'disableStorage' => true]
        ]);

        $this->logger->expects($this->never())->method('error');

        $this->queueDataFactory
            ->expects($this->exactly(1))
            ->method('convertSubmissionToJob')
            ->withConsecutive(
                [$this->submission, 'route1', 0, QueueInterface::STATUS_PENDING]
            )
            ->willReturnOnConsecutiveCalls(...array_values($this->jobs));

        $this->persistentQueue
            ->expects($this->never())
            ->method('addJob');

        $this->temporaryQueue
            ->expects($this->exactly(1))
            ->method('addJob')
            ->withConsecutive(
                [$this->jobs['route1:0']]
            );

        $this->persistentQueueProcessor
            ->expects($this->never())
            ->method('processJobs');

        $this->temporaryQueueProcessor
            ->expects($this->once())
            ->method('processJobs')
            ->with([
                $this->jobs['route1:0'],
            ]);

        $this->subject->process($this->submission);
    }

    /** @test */
    public function processAsyncOneRouteOnePassWithStorage(): void
    {
        $this->initSubmission();
        $this->addRoute('route1', [
            ['async' => true, 'disableStorage' => false]
        ]);

        $this->logger->expects($this->never())->method('error');

        $this->queueDataFactory
            ->expects($this->exactly(1))
            ->method('convertSubmissionToJob')
            ->withConsecutive(
                [$this->submission, 'route1', 0, QueueInterface::STATUS_QUEUED]
            )
            ->willReturnOnConsecutiveCalls(...array_values($this->jobs));

        $this->persistentQueue
            ->expects($this->exactly(1))
            ->method('addJob')
            ->withConsecutive(
                [$this->jobs['route1:0']]
            );

        $this->temporaryQueue
            ->expects($this->never())
            ->method('addJob');

        $this->persistentQueueProcessor
            ->expects($this->never())
            ->method('processJobs');

        $this->temporaryQueueProcessor
            ->expects($this->never())
            ->method('processJobs');

        $this->subject->process($this->submission);
    }

    /** @test */
    public function processSyncOneRouteWithMultiplePasses(): void
    {
        $this->initSubmission();
        $this->addRoute('route1', [
            ['async' => false, 'disableStorage' => false],
            ['async' => false, 'disableStorage' => false]
        ]);

        $this->logger->expects($this->never())->method('error');

        $this->queueDataFactory
            ->expects($this->exactly(2))
            ->method('convertSubmissionToJob')
            ->withConsecutive(
                [$this->submission, 'route1', 0, QueueInterface::STATUS_PENDING],
                [$this->submission, 'route1', 1, QueueInterface::STATUS_PENDING]
            )
            ->willReturnOnConsecutiveCalls(...array_values($this->jobs));

        $this->persistentQueue
            ->expects($this->exactly(2))
            ->method('addJob')
            ->withConsecutive(
                [$this->jobs['route1:0']],
                [$this->jobs['route1:1']]
            );

        $this->temporaryQueue
            ->expects($this->never())
            ->method('addJob');

        $this->persistentQueueProcessor
            ->expects($this->once())
            ->method('processJobs')
            ->with([
                $this->jobs['route1:0'],
                $this->jobs['route1:1'],
            ]);

        $this->temporaryQueueProcessor
            ->expects($this->never())
            ->method('processJobs');

        $this->subject->process($this->submission);
    }

    /** @test */
    public function processAsyncOneRouteWithMultiplePasses(): void
    {
        $this->initSubmission();
        $this->addRoute('route1', [
            ['async' => true, 'disableStorage' => false],
            ['async' => true, 'disableStorage' => false]
        ]);

        $this->logger->expects($this->never())->method('error');

        $this->queueDataFactory
            ->expects($this->exactly(2))
            ->method('convertSubmissionToJob')
            ->withConsecutive(
                [$this->submission, 'route1', 0, QueueInterface::STATUS_QUEUED],
                [$this->submission, 'route1', 1, QueueInterface::STATUS_QUEUED]
            )
            ->willReturnOnConsecutiveCalls(...array_values($this->jobs));

        $this->persistentQueue
            ->expects($this->exactly(2))
            ->method('addJob')
            ->withConsecutive(
                [$this->jobs['route1:0']],
                [$this->jobs['route1:1']]
            );

        $this->temporaryQueue
            ->expects($this->never())
            ->method('addJob');

        $this->persistentQueueProcessor
            ->expects($this->never())
            ->method('processJobs');

        $this->temporaryQueueProcessor
            ->expects($this->never())
            ->method('processJobs');

        $this->subject->process($this->submission);
    }

    /** @test */
    public function processSyncAndAsyncOneRouteWithMultiplePasses(): void
    {
        $this->initSubmission();
        $this->addRoute('route1', [
            ['async' => false, 'disableStorage' => false],
            ['async' => true, 'disableStorage' => false]
        ]);

        $this->logger->expects($this->never())->method('error');

        $this->queueDataFactory
            ->expects($this->exactly(2))
            ->method('convertSubmissionToJob')
            ->withConsecutive(
                [$this->submission, 'route1', 0, QueueInterface::STATUS_PENDING],
                [$this->submission, 'route1', 1, QueueInterface::STATUS_QUEUED]
            )
            ->willReturnOnConsecutiveCalls(...array_values($this->jobs));

        $this->persistentQueue
            ->expects($this->exactly(2))
            ->method('addJob')
            ->withConsecutive(
                [$this->jobs['route1:0']],
                [$this->jobs['route1:1']]
            );

        $this->temporaryQueue
            ->expects($this->never())
            ->method('addJob');

        $this->persistentQueueProcessor
            ->expects($this->once())
            ->method('processJobs')
            ->with([
                $this->jobs['route1:0'],
            ]);

        $this->temporaryQueueProcessor
            ->expects($this->never())
            ->method('processJobs');

        $this->subject->process($this->submission);
    }

    /** @test */
    public function processAsyncWithoutStorageLogsErrorConvertsToSync(): void
    {
        $this->initSubmission();
        $this->addRoute('route1', [
            ['async' => true, 'disableStorage' => true],
        ]);

        $this->logger->expects($this->once())->method('error')->with('Async submissions without storage are not possible. Using sync submission instead.');

        $this->queueDataFactory
            ->expects($this->exactly(1))
            ->method('convertSubmissionToJob')
            ->withConsecutive(
                [$this->submission, 'route1', 0, QueueInterface::STATUS_PENDING]
            )
            ->willReturnOnConsecutiveCalls(...array_values($this->jobs));

        $this->persistentQueue
            ->expects($this->never())
            ->method('addJob');

        $this->temporaryQueue
            ->expects($this->exactly(1))
            ->method('addJob')
            ->withConsecutive(
                [$this->jobs['route1:0']],
            );

        $this->persistentQueueProcessor
            ->expects($this->never())
            ->method('processJobs');

        $this->temporaryQueueProcessor
            ->expects($this->once())
            ->method('processJobs')
            ->with([
                $this->jobs['route1:0'],
            ]);

        $this->subject->process($this->submission);
    }

    /** @test */
    public function processMixedSyncMixedStorageMultipleRoutesWithMultiplePasses(): void
    {
        $this->initSubmission();
        $this->addRoute('route1', [
            ['async' => false, 'disableStorage' => false],
            ['async' => true, 'disableStorage' => false],
        ]);
        $this->addRoute('route2', [
            ['async' => false, 'disableStorage' => true],
            ['async' => true, 'disableStorage' => true], // should be converted to be sync
        ]);

        $this->logger->expects($this->once())->method('error')->with('Async submissions without storage are not possible. Using sync submission instead.');

        $this->queueDataFactory
            ->expects($this->exactly(4))
            ->method('convertSubmissionToJob')
            ->withConsecutive(
                [$this->submission, 'route1', 0, QueueInterface::STATUS_PENDING],
                [$this->submission, 'route1', 1, QueueInterface::STATUS_QUEUED],
                [$this->submission, 'route2', 0, QueueInterface::STATUS_PENDING],
                [$this->submission, 'route2', 1, QueueInterface::STATUS_PENDING]
            )
            ->willReturnOnConsecutiveCalls(...array_values($this->jobs));

        $this->persistentQueue
            ->expects($this->exactly(2))
            ->method('addJob')
            ->withConsecutive(
                [$this->jobs['route1:0']],
                [$this->jobs['route1:1']]
            );

        $this->temporaryQueue
            ->expects($this->exactly(2))
            ->method('addJob')
            ->withConsecutive(
                [$this->jobs['route2:0']],
                [$this->jobs['route2:1']],
            );

        $this->persistentQueueProcessor
            ->expects($this->once())
            ->method('processJobs')
            ->with([
                $this->jobs['route1:0'],
            ]);

        $this->temporaryQueueProcessor
            ->expects($this->once())
            ->method('processJobs')
            ->with([
                $this->jobs['route2:0'],
                $this->jobs['route2:1'],
            ]);

        $this->subject->process($this->submission);
    }

    /** @test */
    public function disabledRouteAsyncWithStorageDoesNotCreateAJobAndIsNotProcessed(): void
    {
        $this->initSubmission();
        $this->addRoute('route1', [
            ['async' => true, 'disableStorage' => false]
        ], enabled:false);

        $this->logger->expects($this->never())->method('error');

        $this->queueDataFactory->expects($this->never())->method('convertSubmissionToJob');
        $this->persistentQueue->expects($this->never())->method('addJob');
        $this->temporaryQueue->expects($this->never())->method('addJob');
        $this->persistentQueueProcessor->expects($this->never())->method('processJobs');
        $this->temporaryQueueProcessor->expects($this->never())->method('processJobs');

        $this->subject->process($this->submission);
    }

    /** @test */
    public function disabledRouteSyncWithStorageDoesNotCreateAJobAndIsNotProcessed(): void
    {
        $this->initSubmission();
        $this->addRoute('route1', [
            ['async' => false, 'disableStorage' => false]
        ], enabled:false);

        $this->logger->expects($this->never())->method('error');

        $this->queueDataFactory->expects($this->never())->method('convertSubmissionToJob');
        $this->persistentQueue->expects($this->never())->method('addJob');
        $this->temporaryQueue->expects($this->never())->method('addJob');
        $this->persistentQueueProcessor->expects($this->never())->method('processJobs');
        $this->temporaryQueueProcessor->expects($this->never())->method('processJobs');

        $this->subject->process($this->submission);
    }

    /** @test */
    public function disabledRouteSyncWithoutStorageDoesNotCreateAJobAndIsNotProcessed(): void
    {
        $this->initSubmission();
        $this->addRoute('route1', [
            ['async' => false, 'disableStorage' => true]
        ], enabled:false);

        $this->logger->expects($this->never())->method('error');

        $this->queueDataFactory->expects($this->never())->method('convertSubmissionToJob');
        $this->persistentQueue->expects($this->never())->method('addJob');
        $this->temporaryQueue->expects($this->never())->method('addJob');
        $this->persistentQueueProcessor->expects($this->never())->method('processJobs');
        $this->temporaryQueueProcessor->expects($this->never())->method('processJobs');

        $this->subject->process($this->submission);
    }
}
