<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry;

use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Queue\QueueProcessor;
use DigitalMarketingFramework\Core\Queue\QueueProcessorInterface;
use DigitalMarketingFramework\Core\Queue\WorkerInterface;
use DigitalMarketingFramework\Core\Registry\Registry as CoreRegistry;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\SchemaDocument;
use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceManagerAwareInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfiguration;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Queue\GlobalConfiguration\Settings\QueueSettings;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\DataDispatcherRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\DataProviderRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Plugin\OutboundRouteRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\ApiRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\DistributorDataSourceRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\QueueDataFactoryRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\Registry\Service\QueueRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\SchemaDocument\RenderingDefinition\Icon;
use DigitalMarketingFramework\Distributor\Core\Service\Distributor;
use DigitalMarketingFramework\Distributor\Core\Service\DistributorInterface;

class Registry extends CoreRegistry implements RegistryInterface
{
    use QueueRegistryTrait;
    use QueueDataFactoryRegistryTrait;
    use DataDispatcherRegistryTrait;
    use DataProviderRegistryTrait;
    use OutboundRouteRegistryTrait;
    use ApiRegistryTrait;
    use DistributorDataSourceRegistryTrait;

    public function processObjectAwareness(object $object): void
    {
        parent::processObjectAwareness($object);

        if ($object instanceof DistributorDataSourceManagerAwareInterface) {
            $object->setDistributorDataSourceManager($this->getDistributorDataSourceManager());
        }
    }

    public function getQueueProcessor(QueueInterface $queue, WorkerInterface $worker): QueueProcessorInterface
    {
        return $this->createObject(QueueProcessor::class, [
            $queue,
            $worker,
            $this->getGlobalConfiguration()->getGlobalSettings(QueueSettings::class),
        ]);
    }

    public function getDistributor(): DistributorInterface
    {
        return $this->createObject(Distributor::class, [$this]);
    }

    public function addConfigurationSchemaDocument(SchemaDocument $schemaDocument): void
    {
        parent::addConfigurationSchemaDocument($schemaDocument);

        // distributor API endpoints
        foreach ($this->getEndPointStorage()->fetchAll() as $endpoint) {
            $schemaDocument->addValueToValueSet('distributorEndPoints/all', $endpoint->getName());
        }

        // general outbound settings
        $generalOutboundConfiguration = new ContainerSchema();
        $generalOutboundConfiguration->getRenderingDefinition()->setIcon(Icon::OUTBOUND_ROUTES);
        $generalOutboundConfiguration->addProperty(DistributorConfigurationInterface::KEY_ASYNC, new BooleanSchema(DistributorConfigurationInterface::DEFAULT_ASYNC));
        $generalOutboundConfiguration->addProperty(DistributorConfigurationInterface::KEY_ENABLE_STORAGE, new BooleanSchema(DistributorConfigurationInterface::DEFAULT_ENABLE_STORAGE));

        $generalIntegration = $this->getGeneralIntegrationSchema($schemaDocument);
        $generalIntegration->addProperty(DistributorConfigurationInterface::KEY_OUTBOUND_ROUTES, $generalOutboundConfiguration);

        // outbound routes integrations
        $this->addOutboundRouteSchema($schemaDocument);

        // data providers
        $dataProcessingSchema = $this->getDataProcessingSchema($schemaDocument);
        $dataProcessingSchema->addProperty(DistributorConfiguration::KEY_DATA_PROVIDERS, $this->getDataProviderSchema());
    }
}
