<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry;

use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Queue\QueueProcessorInterface;
use DigitalMarketingFramework\Core\Queue\WorkerInterface;
use DigitalMarketingFramework\Core\Registry\RegistryInterface as CoreRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\DataDispatcherRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\DataProviderRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\RouteRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\QueueDataFactoryRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\QueueRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Service\RelayInterface;

interface RegistryInterface extends
    CoreRegistryInterface,
    QueueRegistryInterface,
    QueueDataFactoryRegistryInterface,
    DataDispatcherRegistryInterface,
    DataProviderRegistryInterface,
    RouteRegistryInterface
{
    public function getQueueProcessor(QueueInterface $queue, WorkerInterface $worker): QueueProcessorInterface;

    public function getRelay(): RelayInterface;
}
