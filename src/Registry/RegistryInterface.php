<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry;

use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Queue\QueueProcessorInterface;
use DigitalMarketingFramework\Core\Queue\WorkerInterface;
use DigitalMarketingFramework\Core\Registry\RegistryInterface as CoreRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\DataDispatcherRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\DataProviderRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\OutboundRouteRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\ApiRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\DistributorDataSourceRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\QueueDataFactoryRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\QueueRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Service\DistributorInterface;

interface RegistryInterface extends
    CoreRegistryInterface,
    QueueRegistryInterface,
    QueueDataFactoryRegistryInterface,
    DataDispatcherRegistryInterface,
    DataProviderRegistryInterface,
    OutboundRouteRegistryInterface,
    ApiRegistryInterface,
    DistributorDataSourceRegistryInterface
{
    public function getQueueProcessor(QueueInterface $queue, WorkerInterface $worker): QueueProcessorInterface;

    public function getDistributor(): DistributorInterface;
}
