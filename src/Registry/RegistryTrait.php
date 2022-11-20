<?php

namespace DigitalMarketingFramework\Distributer\Core\Registry;

use DigitalMarketingFramework\Core\Log\LoggerFactoryInterface;
use DigitalMarketingFramework\Core\Log\NullLoggerFactory;
use DigitalMarketingFramework\Core\Queue\NonPersistentQueue;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Queue\QueueProcessor;
use DigitalMarketingFramework\Core\Queue\QueueProcessorInterface;
use DigitalMarketingFramework\Core\Queue\WorkerInterface;
use DigitalMarketingFramework\Core\Registry\Plugin\ConfigurationResolverRegistryTrait;
use DigitalMarketingFramework\Core\Registry\Registry as CoreRegistry;
use DigitalMarketingFramework\Core\Registry\Service\QueueRegistryTrait;
use DigitalMarketingFramework\Core\Request\DefaultRequest;
use DigitalMarketingFramework\Core\Request\RequestInterface;
use DigitalMarketingFramework\Distributer\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributer\Core\Factory\QueueDataFactoryInterface;
use DigitalMarketingFramework\Distributer\Core\Model\Configuration\SubmissionConfigurationInterface;
use DigitalMarketingFramework\Distributer\Core\Registry\Plugin\DataDispatcherRegistryTrait;
use DigitalMarketingFramework\Distributer\Core\Registry\Plugin\DataProviderRegistryTrait;
use DigitalMarketingFramework\Distributer\Core\Registry\Plugin\RouteRegistryTrait;
use DigitalMarketingFramework\Distributer\Core\Registry\Service\QueueDataFactoryRegistryTrait;
use DigitalMarketingFramework\Distributer\Core\Service\Relay;
use DigitalMarketingFramework\Distributer\Core\Service\RelayInterface;

trait RegistryTrait
{
    use QueueRegistryTrait;
    use QueueDataFactoryRegistryTrait;
    use ConfigurationResolverRegistryTrait;
    use DataDispatcherRegistryTrait;
    use DataProviderRegistryTrait;
    use RouteRegistryTrait;

    public function getQueueProcessor(QueueInterface $queue, WorkerInterface $worker): QueueProcessorInterface
    {
        return new QueueProcessor($queue, $worker);
    }

    public function getRelay(): RelayInterface
    {
        return $this->createObject(Relay::class, [$this]);
    }

    public function getDefaultConfiguration(): array
    {
        $defaultConfig = Relay::getDefaultConfiguration();
        $defaultConfig[SubmissionConfigurationInterface::KEY_DATA_PROVIDERS] = $this->getDataProviderDefaultConfigurations();
        $defaultConfig[SubmissionConfigurationInterface::KEY_ROUTES] = $this->getRouteDefaultConfigurations();
        return $defaultConfig;
    }
}
