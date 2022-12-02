<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\Integration;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Log\LoggerFactoryInterface;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Distributer\Core\CoreInitialization;
use DigitalMarketingFramework\Distributer\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributer\Core\Factory\QueueDataFactoryInterface;
use DigitalMarketingFramework\Distributer\Core\Registry\Registry;
use DigitalMarketingFramework\Distributer\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributer\Core\Tests\Spy\DataDispatcher\DataDispatcherSpyInterface;
use DigitalMarketingFramework\Distributer\Core\Tests\Spy\DataProvider\DataProviderSpyInterface;
use DigitalMarketingFramework\Distributer\Core\Tests\Spy\DataProvider\SpiedOnGenericDataProvider;
use DigitalMarketingFramework\Distributer\Core\Tests\Spy\Route\RouteSpyInterface;
use DigitalMarketingFramework\Distributer\Core\Tests\Spy\Route\SpiedOnGenericRoute;
use PHPUnit\Framework\MockObject\MockObject;

trait RegistryTestTrait // extends \PHPUnit\Framework\TestCase
{
    protected ContextInterface&MockObject $context;

    protected LoggerFactoryInterface&MockObject $loggerFactory;

    protected QueueInterface&MockObject $queue;

    protected QueueInterface&MockObject $temporaryQueue;

    protected QueueDataFactoryInterface $queueDataFactory;

    protected RegistryInterface $registry;

    protected RouteSpyInterface&MockObject $routeSpy;

    protected DataProviderSpyInterface&MockObject $dataProviderSpy;

    protected DataDispatcherSpyInterface&MockObject $dataDispatcherSpy;

    protected function initRegistry(): void
    {
        // mock everything from the outside world
        $this->context = $this->createMock(ContextInterface::class);
        $this->loggerFactory = $this->createMock(LoggerFactoryInterface::class);
        $this->queue = $this->createMock(QueueInterface::class);
        $this->temporaryQueue = $this->createMock(QueueInterface::class);

        // initialize the rest regularly
        $this->queueDataFactory = new QueueDataFactory();

        $this->registry = new Registry(
            context:$this->context, 
            loggerFactory:$this->loggerFactory, 
            persistentQueue:$this->queue, 
            nonPersistentQueue:$this->temporaryQueue, 
            queueDataFactory:$this->queueDataFactory
        );
        CoreInitialization::initialize($this->registry);
    }

    protected function registerRouteSpy(): RouteSpyInterface&MockObject
    {
        $this->routeSpy = $this->createMock(RouteSpyInterface::class);
        $this->registry->registerRoute(SpiedOnGenericRoute::class, [$this->routeSpy], 'generic');
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
        $this->registry->registerDataDispatcher(SpiedOnGenericDataDispatcher::class, [$this->dataDispatcherSpy]);
        return $this->dataDispatcherSpy;
    }
}
