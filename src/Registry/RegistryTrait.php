<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry;

use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Queue\QueueProcessor;
use DigitalMarketingFramework\Core\Queue\QueueProcessorInterface;
use DigitalMarketingFramework\Core\Queue\WorkerInterface;
use DigitalMarketingFramework\Core\Registry\Plugin\ConfigurationResolverRegistryTrait;
use DigitalMarketingFramework\Core\Registry\Plugin\IdentifierCollectorRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\DataDispatcherRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\DataProviderRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\RouteRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\QueueDataFactoryRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\QueueRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Service\Relay;
use DigitalMarketingFramework\Distributor\Core\Service\RelayInterface;

trait RegistryTrait
{
    use QueueRegistryTrait;
    use QueueDataFactoryRegistryTrait;
    use ConfigurationResolverRegistryTrait;
    use DataDispatcherRegistryTrait;
    use DataProviderRegistryTrait;
    use RouteRegistryTrait;
    use IdentifierCollectorRegistryTrait;

    public function getQueueProcessor(QueueInterface $queue, WorkerInterface $worker): QueueProcessorInterface
    {
        return $this->createObject(QueueProcessor::class, [$queue, $worker]);
    }

    public function getRelay(): RelayInterface
    {
        return $this->createObject(Relay::class, [$this]);
    }

    public function getDefaultRelayConfiguration(): array
    {
        $defaultConfig = Relay::getDefaultConfiguration();
        $defaultConfig[SubmissionConfigurationInterface::KEY_DATA_PROVIDERS] = $this->getDataProviderDefaultConfigurations();
        $defaultConfig[SubmissionConfigurationInterface::KEY_ROUTES] = $this->getRouteDefaultConfigurations();
        return $defaultConfig;
    }

    public function getDefaultConfiguration(): array
    {
        return $this->getDefaultRelayConfiguration();
    }
}
