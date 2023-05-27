<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\SchemaDocument;
use DigitalMarketingFramework\Core\Registry\Registry as CoreRegistry;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Queue\QueueProcessor;
use DigitalMarketingFramework\Core\Queue\QueueProcessorInterface;
use DigitalMarketingFramework\Core\Queue\WorkerInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfiguration;
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

    public function addConfigurationSchema(SchemaDocument $schemaDocument): void
    {
        parent::addConfigurationSchema($schemaDocument);

        $distributorSchema = new ContainerSchema();

        $distributorSchema->addProperty('async', new BooleanSchema(false));
        $distributorSchema->addProperty('disableStorage', new BooleanSchema(false));

        $routeListSchema = $this->getRoutesSchema($schemaDocument);
        $distributorSchema->addProperty(SubmissionConfiguration::KEY_ROUTES, $routeListSchema);

        $distributorSchema->addProperty(SubmissionConfiguration::KEY_DATA_PROVIDERS, $this->getDataProviderSchema());

        $schemaDocument->getMainSchema()->addProperty(SubmissionConfigurationInterface::KEY_DISTRIBUTOR, $distributorSchema);
    }
}
