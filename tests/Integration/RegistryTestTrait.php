<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration;

use DigitalMarketingFramework\Core\ConfigurationDocument\ConfigurationDocumentManagerInterface;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\CorePluginInitialization;
use DigitalMarketingFramework\Distributor\Core\CorePluginInitialization as DistributorCorePluginInitalization;
use DigitalMarketingFramework\Core\Log\LoggerFactoryInterface;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Distributor\Core\DistributorPluginInitialization;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactoryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Registry;
use DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataDispatcher\DataDispatcherSpyInterface;
use DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataProvider\DataProviderSpyInterface;
use DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataProvider\SpiedOnGenericDataProvider;
use DigitalMarketingFramework\Distributor\Core\Tests\Spy\Route\RouteSpyInterface;
use DigitalMarketingFramework\Distributor\Core\Tests\Spy\Route\SpiedOnGenericRoute;
use PHPUnit\Framework\MockObject\MockObject;

trait RegistryTestTrait // extends \PHPUnit\Framework\TestCase
{
    protected ContextInterface&MockObject $context;

    protected LoggerFactoryInterface&MockObject $loggerFactory;

    protected QueueInterface&MockObject $queue;

    protected QueueInterface&MockObject $temporaryQueue;

    protected ConfigurationDocumentManagerInterface&MockObject $configurationDocumentManager;

    protected QueueDataFactoryInterface $queueDataFactory;

    protected Registry $registry;

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
        $this->configurationDocumentManager = $this->createMock(ConfigurationDocumentManagerInterface::class);
        $this->configurationDocumentManager->method('getConfigurationStackFromConfiguration')->willReturnCallback(function($configuration) {
            return [$configuration];
        });

        // initialize the rest regularly
        $this->queueDataFactory = new QueueDataFactory($this->configurationDocumentManager);

        $this->registry = new Registry();
        $this->registry->setContext($this->context);
        $this->registry->setLoggerFactory($this->loggerFactory);
        $this->registry->setPersistentQueue($this->queue);
        $this->registry->setNonPersistentQueue($this->temporaryQueue);
        $this->registry->setQueueDataFactory($this->queueDataFactory);
        CorePluginInitialization::initialize($this->registry);
        DistributorCorePluginInitalization::initialize($this->registry);
        DistributorPluginInitialization::initialize($this->registry);
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
