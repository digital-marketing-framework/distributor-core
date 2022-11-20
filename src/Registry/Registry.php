<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry;

use DigitalMarketingFramework\Core\Registry\Registry as CoreRegistry;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Queue\QueueProcessor;
use DigitalMarketingFramework\Core\Queue\QueueProcessorInterface;
use DigitalMarketingFramework\Core\Queue\WorkerInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\DataDispatcherRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\DataProviderRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\RouteRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\QueueDataFactoryRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\QueueRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Service\Relay;
use DigitalMarketingFramework\Distributor\Core\Service\RelayInterface;

class Registry extends CoreRegistry implements RegistryInterface
{
    use QueueRegistryTrait;
    use QueueDataFactoryRegistryTrait;
    use DataDispatcherRegistryTrait;
    use DataProviderRegistryTrait;
    use RouteRegistryTrait;

    public function getQueueProcessor(QueueInterface $queue, WorkerInterface $worker): QueueProcessorInterface
    {
        return $this->createObject(QueueProcessor::class, [$queue, $worker]);
    }

    public function getRelay(): RelayInterface
    {
        return $this->createObject(Relay::class, [$this]);
    }

    public function getDistributorDefaultConfiguration(): array
    {
        $defaultDistributorConfiguration = Relay::getDefaultConfiguration();
        $defaultDistributorConfiguration[SubmissionConfigurationInterface::KEY_DATA_PROVIDERS] = $this->getDataProviderDefaultConfigurations();
        $defaultDistributorConfiguration[SubmissionConfigurationInterface::KEY_ROUTES] = $this->getRouteDefaultConfigurations();
        return $defaultDistributorConfiguration;
    }

    public function getDefaultConfiguration(): array
    {
        $defaultConfiguration = parent::getDefaultConfiguration();
        $defaultConfiguration[SubmissionConfigurationInterface::KEY_DISTRIBUTOR] = $this->getDistributorDefaultConfiguration();
        return $defaultConfiguration;
    }

    public function getConfigurationSchema(): array
    {
        return parent::getConfigurationSchema();
    }
}
