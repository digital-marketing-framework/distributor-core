<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration;

use DigitalMarketingFramework\Core\DataPrivacy\UnregulatedDataPrivacyPlugin;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Registry\RegistryCollection;
use DigitalMarketingFramework\Core\Registry\RegistryCollectionInterface;
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

    protected RegistryCollectionInterface $registryCollection;

    protected QueueInterface&MockObject $queue;

    protected QueueInterface&MockObject $temporaryQueue;

    protected QueueDataFactoryInterface $queueDataFactory;

    protected function createRegistry(): void
    {
        $this->registryCollection = new RegistryCollection();
        $this->registry = new Registry();

        // TODO do we need to create a core registry for accurate tests?
        $this->registryCollection->addRegistry(RegistryDomain::CORE, $this->registry);

        $this->registryCollection->addRegistry(RegistryDomain::DISTRIBUTOR, $this->registry);
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
        $this->registry->getDataPrivacyManager()->addPlugin($this->registry->createObject(UnregulatedDataPrivacyPlugin::class));

        // init plugins
        $distributorCoreInitialization = new DistributorCoreInitialization();
        $distributorCoreInitialization->initMetaData($this->registry);
        // NOTE core initialization has no global config or services to initialize customly
        //      but other integrations will want to call those methods on their init object
        // $distributorCoreInitialization->initGlobalConfiguration(RegistryDomain::CORE, $this->registry);
        // $distributorCoreInitialization->initGlobalConfiguration(RegistryDomain::DISTRIBUTOR, $this->registry);
        // $distributorCoreInitialization->initServices(RegistryDomain::CORE, $this->registry);
        // $distributorCoreInitialization->initServices(RegistryDomain::DISTRIBUTOR, $this->registry);
        $distributorCoreInitialization->initPlugins(RegistryDomain::CORE, $this->registry);
        $distributorCoreInitialization->initPlugins(RegistryDomain::DISTRIBUTOR, $this->registry);
    }
}
