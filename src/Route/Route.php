<?php

namespace DigitalMarketingFramework\Distributor\Core\Route;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\Custom\InheritableBooleanSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\DataProcessor\DataMapperSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\DataProcessor\EvaluationSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessor;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessorAwareInterface;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessorAwareTrait;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessorContextInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Helper\ConfigurationTrait;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Plugin\Plugin;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Service\RelayInterface;

abstract class Route extends Plugin implements RouteInterface, DataProcessorAwareInterface
{
    use ConfigurationTrait;
    use DataProcessorAwareTrait;

    protected const DEFAULT_ASYNC = InheritableBooleanSchema::VALUE_INHERIT;
    protected const DEFAULT_DISABLE_STORAGE = InheritableBooleanSchema::VALUE_INHERIT;

    protected const KEY_ENABLE_DATA_PROVIDERS = 'enableDataProviders';
    protected const DEFAULT_ENABLE_DATA_PROVIDERS = '*';

    public const MESSAGE_GATE_FAILED = 'Gate not passed for route "%s" in pass %d.';
    public const MESSAGE_DATA_EMPTY = 'No data generated for route "%s" in pass %d.';
    public const MESSAGE_DISPATCHER_NOT_FOUND = 'No dispatcher found for route "%s" in pass %d.';

    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        protected SubmissionDataSetInterface $submission,
        protected int $pass,
    ) {
        parent::__construct($keyword, $registry);
        $this->configuration = $this->submission->getConfiguration()->getRoutePassConfiguration($this->getKeyword(), $this->pass);
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

    public function getPass(): int
    {
        return $this->pass;
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
        return GeneralUtility::castValueToArray(
            $this->getConfig(static::KEY_ENABLE_DATA_PROVIDERS)
        );
    }

    public function addContext(ContextInterface $context): void
    {
    }

    public function process(): bool
    {
        if (!$this->processGate()) {
            $this->logger->debug(sprintf(static::MESSAGE_GATE_FAILED, $this->getKeyword(), $this->pass));
            return false;
        }

        $data = $this->buildData();

        if (GeneralUtility::isEmpty($data)) {
            throw new DigitalMarketingFrameworkException(sprintf(static::MESSAGE_DATA_EMPTY, $this->getKeyword(), $this->pass));
        }

        $dataDispatcher = $this->getDispatcher();
        if (!$dataDispatcher) {
            throw new DigitalMarketingFrameworkException(sprintf(static::MESSAGE_DISPATCHER_NOT_FOUND, $this->getKeyword(), $this->pass));
        }

        $dataDispatcher->send($data->toArray());
        return true;
    }

    abstract protected function getDispatcher(): ?DataDispatcherInterface;

    public static function getDefaultConfiguration(): array
    {
        return [
            static::KEY_ENABLED => static::DEFAULT_ENABLED,
            RelayInterface::KEY_ASYNC => static::DEFAULT_ASYNC,
            RelayInterface::KEY_DISABLE_STORAGE => static::DEFAULT_DISABLE_STORAGE,
            static::KEY_ENABLE_DATA_PROVIDERS => static::DEFAULT_ENABLE_DATA_PROVIDERS,
            static::KEY_GATE => DataProcessor::getDefaultEvaluationConfiguration(),
            static::KEY_DATA => DataProcessor::getDefaultDataMapperConfiguration(),
            // TODO: static::KEY_MARKETING_CONSENT => static::DEFAULT_MARKETING_CONSENT?
        ];
    }

    public static function getSchema(): SchemaInterface
    {
        $schema = new ContainerSchema();
        $schema->addProperty(static::KEY_ENABLED, new BooleanSchema(static::DEFAULT_ENABLED));

        $schema->addProperty(RelayInterface::KEY_ASYNC, new InheritableBooleanSchema());
        $schema->addProperty(RelayInterface::KEY_DISABLE_STORAGE, new InheritableBooleanSchema());

        $schema->addProperty(static::KEY_ENABLE_DATA_PROVIDERS, new StringSchema(static::DEFAULT_ENABLE_DATA_PROVIDERS));
        $schema->addProperty(static::KEY_GATE, new CustomSchema(EvaluationSchema::TYPE));
        $schema->addProperty(static::KEY_DATA, new CustomSchema(DataMapperSchema::TYPE));

        return $schema;
    }
}
