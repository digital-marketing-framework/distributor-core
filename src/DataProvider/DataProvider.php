<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\RenderingDefinition\RenderingDefinitionInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Plugin\ConfigurablePlugin;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;

abstract class DataProvider extends ConfigurablePlugin implements DataProviderInterface
{
    public const KEY_ENABLED = 'enabled';

    public const DEFAULT_ENABLED = false;

    public const KEY_MUST_EXIST = 'mustExist';

    public const DEFAULT_MUST_EXIST = false;

    public const KEY_MUST_BE_EMPTY = 'mustBeEmpty';

    public const DEFAULT_MUST_BE_EMPTY = true;

    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        protected SubmissionDataSetInterface $submission
    ) {
        parent::__construct($keyword, $registry);
        $this->configuration = $this->submission->getConfiguration()->getDataProviderConfiguration($this->getKeyword());
    }

    abstract protected function processContext(ContextInterface $context): void;

    abstract protected function process(): void;

    /**
     * Public information on whether the data provider is enabled.
     * Can be used from outside to consider whether or not it should even be called or its configuration stored.
     */
    public function enabled(): bool
    {
        return (bool) $this->getConfig(static::KEY_ENABLED);
    }

    /**
     * Internal information on whether the data provider should proceed adding data.
     * An enabled data provider may still have a reason not to add data,
     * which is why this is different form the method enabled().
     */
    protected function proceed(): bool
    {
        return $this->enabled();
    }

    protected function appendToField(string $key, string|ValueInterface $value, string $glue = "\n"): bool
    {
        $data = $this->submission->getData();
        if (
            $this->getConfig(static::KEY_MUST_EXIST)
            && !$data->fieldExists($key)
        ) {
            return false;
        }

        if ($data->fieldEmpty($key)) {
            $data[$key] = $value;
        } else {
            $data[$key] .= $glue . $value;
        }

        return true;
    }

    protected function setField(string $key, string|ValueInterface $value): bool
    {
        $data = $this->submission->getData();
        if (
            $this->getConfig(static::KEY_MUST_EXIST)
            && !$data->fieldExists($key)
        ) {
            return false;
        }

        if (
            $this->getConfig(static::KEY_MUST_BE_EMPTY)
            && $data->fieldExists($key)
            && !$data->fieldEmpty($key)
        ) {
            return false;
        }

        $data[$key] = $value;

        return true;
    }

    public function addData(): void
    {
        if ($this->proceed()) {
            $this->process();
        }
    }

    public function addContext(ContextInterface $context): void
    {
        if ($this->enabled()) {
            $this->processContext($context);
        }
    }

    public static function getSchema(): SchemaInterface
    {
        $schema = new ContainerSchema();
        $schema->addProperty(static::KEY_ENABLED, new BooleanSchema(static::DEFAULT_ENABLED));

        $mustExistSchema = new BooleanSchema(static::DEFAULT_MUST_EXIST);
        $mustExistSchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $schema->addProperty(static::KEY_MUST_EXIST, $mustExistSchema);

        $mustBeEmptySchema = new BooleanSchema(static::DEFAULT_MUST_BE_EMPTY);
        $mustBeEmptySchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $schema->addProperty(static::KEY_MUST_BE_EMPTY, $mustBeEmptySchema);

        return $schema;
    }
}
