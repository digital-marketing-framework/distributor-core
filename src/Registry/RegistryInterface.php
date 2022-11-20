<?php

namespace DigitalMarketingFramework\Distributer\Core\Registry;

use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Queue\QueueProcessorInterface;
use DigitalMarketingFramework\Core\Queue\WorkerInterface;
use DigitalMarketingFramework\Core\Registry\Plugin\ConfigurationResolverRegistryInterface;
use DigitalMarketingFramework\Core\Registry\RegistryInterface as CoreRegistryInterface;
use DigitalMarketingFramework\Core\Registry\Service\QueueRegistryInterface;
use DigitalMarketingFramework\Distributer\Core\Registry\Plugin\DataDispatcherRegistryInterface;
use DigitalMarketingFramework\Distributer\Core\Registry\Plugin\DataProviderRegistryInterface;
use DigitalMarketingFramework\Distributer\Core\Registry\Plugin\RouteRegistryInterface;
use DigitalMarketingFramework\Distributer\Core\Registry\Service\QueueDataFactoryRegistryInterface;
use DigitalMarketingFramework\Distributer\Core\Service\RelayInterface;

interface RegistryInterface extends 
    CoreRegistryInterface, 
    QueueRegistryInterface, 
    QueueDataFactoryRegistryInterface, 
    ConfigurationResolverRegistryInterface, 
    DataDispatcherRegistryInterface, 
    DataProviderRegistryInterface, 
    RouteRegistryInterface
{
    public function getQueueProcessor(QueueInterface $queue, WorkerInterface $worker): QueueProcessorInterface;

    public function getRelay(): RelayInterface;

    public function getDefaultConfiguration(): array;
}
