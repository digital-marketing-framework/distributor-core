<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextAwareInterface;
use DigitalMarketingFramework\Core\Context\ContextAwareTrait;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\DataPrivacy\DataPrivacyManagerAwareInterface;
use DigitalMarketingFramework\Core\DataPrivacy\DataPrivacyManagerAwareTrait;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Core\SchemaDocument\RenderingDefinition\RenderingDefinitionInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\DataPrivacyPermissionSelectionSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Plugin\ConfigurablePlugin;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\SchemaDocument\RenderingDefinition\Icon;

abstract class DataProvider extends ConfigurablePlugin implements DataProviderInterface, ContextAwareInterface, DataPrivacyManagerAwareInterface
{
    use ContextAwareTrait;
    use DataPrivacyManagerAwareTrait;

    public const KEY_ENABLED = 'enabled';

    public const DEFAULT_ENABLED = false;

    public const KEY_REQUIRED_PERMISSION = 'requiredPermission';

    public const KEY_MUST_EXIST = 'mustExist';

    public const DEFAULT_MUST_EXIST = false;

    public const KEY_MUST_BE_EMPTY = 'mustBeEmpty';

    public const DEFAULT_MUST_BE_EMPTY = true;

    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        protected SubmissionDataSetInterface $submission,
    ) {
        parent::__construct($keyword, $registry);
        $this->configuration = $this->submission->getConfiguration()->getDataProviderConfiguration($this->getKeyword());
    }

    abstract protected function processContext(WriteableContextInterface $context): void;

    abstract protected function process(): void;

    /**
     * Public information on whether the data provider is enabled.
     * Can be used from outside to consider whether it should even be called or its configuration stored.
     */
    public function enabled(): bool
    {
        return (bool)$this->getConfig(static::KEY_ENABLED);
    }

    public function allowed(): bool
    {
        $permission = $this->getConfig(static::KEY_REQUIRED_PERMISSION);

        return $this->dataPrivacyManager->getPermission($permission);
    }

    /**
     * Internal information on whether the data provider should proceed adding data.
     */
    protected function proceed(): bool
    {
        return $this->enabled() && $this->allowed();
    }

    protected function appendToField(string $key, string|ValueInterface $value, string $glue = "\n"): bool
    {
        $data = $this->submission->getData();
        if (
            $this->getBoolConfig(static::KEY_MUST_EXIST)
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
            $this->getBoolConfig(static::KEY_MUST_EXIST)
            && !$data->fieldExists($key)
        ) {
            return false;
        }

        if (
            $this->getBoolConfig(static::KEY_MUST_BE_EMPTY)
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

    public function addDataForPreview(): void
    {
        $this->addData();
    }

    public function addContext(WriteableContextInterface $context): void
    {
        if ($this->enabled()) {
            $this->processContext($context);
        }
    }

    public static function getSchema(): SchemaInterface
    {
        $schema = new ContainerSchema();
        $schema->getRenderingDefinition()->setIcon(Icon::DATA_PROVIDER);

        $label = static::getLabel();
        if ($label !== null) {
            $schema->getRenderingDefinition()->setLabel($label);
        }

        $schema->addProperty(static::KEY_ENABLED, new BooleanSchema(static::DEFAULT_ENABLED));

        $schema->addProperty(static::KEY_REQUIRED_PERMISSION, new CustomSchema(DataPrivacyPermissionSelectionSchema::TYPE));

        $mustExistSchema = new BooleanSchema(static::DEFAULT_MUST_EXIST);
        $mustExistSchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $schema->addProperty(static::KEY_MUST_EXIST, $mustExistSchema);

        $mustBeEmptySchema = new BooleanSchema(static::DEFAULT_MUST_BE_EMPTY);
        $mustBeEmptySchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $schema->addProperty(static::KEY_MUST_BE_EMPTY, $mustBeEmptySchema);

        return $schema;
    }
}
