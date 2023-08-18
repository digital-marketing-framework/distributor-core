<?php

namespace DigitalMarketingFramework\Distributor\Core\Route;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\Custom\InheritableBooleanSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\Custom\RestrictedTermsSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\DataProcessor\DataMapperSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\DataProcessor\EvaluationSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessorAwareInterface;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessorAwareTrait;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessorContextInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Plugin\ConfigurablePlugin;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Service\RelayInterface;

abstract class Route extends ConfigurablePlugin implements RouteInterface, DataProcessorAwareInterface
{
    use DataProcessorAwareTrait;

    protected const DEFAULT_ASYNC = InheritableBooleanSchema::VALUE_INHERIT;
    protected const DEFAULT_DISABLE_STORAGE = InheritableBooleanSchema::VALUE_INHERIT;

    protected const KEY_ENABLE_DATA_PROVIDERS = 'enableDataProviders';

    public const MESSAGE_GATE_FAILED = 'Gate not passed for route "%s" with index %d.';
    public const MESSAGE_DATA_EMPTY = 'No data generated for route "%s" with index %d.';

    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        protected SubmissionDataSetInterface $submission,
        protected int $index,
    ) {
        parent::__construct($keyword, $registry);
        $this->configuration = $this->submission->getConfiguration()->getRoutePassConfiguration($this->index);
    }

    protected function buildData(): DataInterface
    {
        return $this->dataProcessor->processDataMapper(
            $this->getConfig(static::KEY_DATA),
            $this->submission->getData(),
            $this->submission->getConfiguration()
        );
    }

    protected function getDataProcessorContext(): DataProcessorContextInterface
    {
        return $this->dataProcessor->createContext(
            $this->submission->getData(),
            $this->submission->getConfiguration()
        );
    }

    protected function processGate(): bool
    {
        if (!$this->enabled()) {
            return false;
        }
        $gate = $this->getConfig(static::KEY_GATE);
        if (empty($gate)) {
            return true;
        }
        return $this->dataProcessor->processEvaluation(
            $this->getConfig(static::KEY_GATE),
            $this->getDataProcessorContext()
        );
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function enabled(): bool
    {
        return (bool)$this->getConfig(static::KEY_ENABLED);
    }

    public function async(): ?bool
    {
        return InheritableBooleanSchema::convert($this->getConfig(RelayInterface::KEY_ASYNC));
    }

    public function disableStorage(): ?bool
    {
        return InheritableBooleanSchema::convert($this->getConfig(RelayInterface::KEY_DISABLE_STORAGE));
    }

    public function getEnabledDataProviders(): array
    {
        $config = $this->getConfig(static::KEY_ENABLE_DATA_PROVIDERS);
        return RestrictedTermsSchema::getAllowedTerms($config);
    }

    public function addContext(ContextInterface $context): void
    {
    }

    public function process(): bool
    {
        if (!$this->processGate()) {
            $this->logger->debug(sprintf(static::MESSAGE_GATE_FAILED, $this->getKeyword(), $this->index));
            return false;
        }

        $data = $this->buildData();

        if (GeneralUtility::isEmpty($data)) {
            throw new DigitalMarketingFrameworkException(sprintf(static::MESSAGE_DATA_EMPTY, $this->getKeyword(), $this->index));
        }

        $dataDispatcher = $this->getDispatcher();
        $dataDispatcher->send($data->toArray());
        return true;
    }

    abstract protected function getDispatcher(): DataDispatcherInterface;

    public static function getSchema(): SchemaInterface
    {
        $schema = new ContainerSchema();
        $schema->getRenderingDefinition()->setNavigationItem(false);

        $enabledProperty = $schema->addProperty(static::KEY_ENABLED, new BooleanSchema(static::DEFAULT_ENABLED));
        $enabledProperty->setWeight(10);

        $schema->addProperty(RelayInterface::KEY_ASYNC, new InheritableBooleanSchema());
        $schema->addProperty(RelayInterface::KEY_DISABLE_STORAGE, new InheritableBooleanSchema());

        $enableDataProviders = new RestrictedTermsSchema('/distributor/dataProviders/*');
        $enableDataProviders->getTypeSchema()->getRenderingDefinition()->setLabel('Enable Data Providers');
        $enableDataProviders->getRenderingDefinition()->setSkipHeader(true);
        $schema->addProperty(static::KEY_ENABLE_DATA_PROVIDERS, $enableDataProviders);

        $gateSchema = new CustomSchema(EvaluationSchema::TYPE);
        $gateSchema->getRenderingDefinition()->setLabel('Gate');
        $schema->addProperty(static::KEY_GATE, $gateSchema);

        $schema->addProperty(static::KEY_DATA, new CustomSchema(DataMapperSchema::TYPE));

        // TODO gdpr should not be handled in the gate. we need a dedicated service for that

        return $schema;
    }
}
