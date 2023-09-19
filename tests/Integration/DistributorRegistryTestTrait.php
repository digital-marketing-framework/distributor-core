<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration;

use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Core\Tests\Integration\RegistryTestTrait;
use DigitalMarketingFramework\Distributor\Core\DistributorCoreInitialization;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactoryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Registry;
use PHPUnit\Framework\MockObject\MockObject;

trait DistributorRegistryTestTrait // extends \PHPUnit\Framework\TestCase
{
    use RegistryTestTrait {
        initRegistry as initCoreRegistry;
    }

    protected Registry $registry;

    protected QueueInterface&MockObject $queue;

    protected QueueInterface&MockObject $temporaryQueue;

    protected QueueDataFactoryInterface $queueDataFactory;

    protected function createRegistry(): void
    {
        $this->registry = new Registry();
    }

    protected function initRegistry(): void
    {
        $this->initCoreRegistry();

        // mock everything from the outside world
        $this->queue = $this->createMock(QueueInterface::class);
        $this->temporaryQueue = $this->createMock(QueueInterface::class);

        // initialize the rest regularly
        $this->queueDataFactory = new QueueDataFactory($this->configurationDocumentManager);
        $this->registry->setPersistentQueue($this->queue);
        $this->registry->setNonPersistentQueue($this->temporaryQueue);
        $this->registry->setQueueDataFactory($this->queueDataFactory);

        // init plugins
        $distributorCoreInitialization = new DistributorCoreInitialization();
        $distributorCoreInitialization->init(RegistryDomain::CORE, $this->registry);
        $distributorCoreInitialization->init(RegistryDomain::DISTRIBUTOR, $this->registry);
    }
}
