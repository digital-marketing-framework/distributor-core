<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\ConfigurationResolver\Context\ConfigurationResolverContext;
use DigitalMarketingFramework\Core\ConfigurationResolver\Context\ConfigurationResolverContextInterface;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Helper\ConfigurationResolverTrait;
use DigitalMarketingFramework\Core\Helper\ConfigurationTrait;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Plugin\Plugin;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;

abstract class DataProvider extends Plugin implements DataProviderInterface
{
    use ConfigurationTrait;
    use ConfigurationResolverTrait;

    const KEY_ENABLED = 'enabled';
    const DEFAULT_ENABLED = false;

    const KEY_MUST_EXIST = 'mustExist';
    const DEFAULT_MUST_EXIST = false;

    const KEY_MUST_BE_EMPTY= 'mustBeEmpty';
    const DEFAULT_MUST_BE_EMPTY = true;

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

    protected function getConfigurationResolverContext(): ConfigurationResolverContextInterface
    {
        return new ConfigurationResolverContext(
            $this->submission->getData(), 
            ['configuration' => $this->submission->getConfiguration()]
        );
    }

    /**
     * Public information on whether the data provider is enabled.
     * Can be used from outside to consider whether or not it should even be called or its configuration stored.
     */
    public function enabled(): bool
    {
        return (bool)$this->getConfig(static::KEY_ENABLED);
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

    protected function appendToField($key, $value, $glue = "\n"): bool
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

    protected function setField($key, $value): bool
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

    public static function getDefaultConfiguration(): array
    {
        return [
            static::KEY_ENABLED => static::DEFAULT_ENABLED,
            static::KEY_MUST_EXIST => static::DEFAULT_MUST_EXIST,
            static::KEY_MUST_BE_EMPTY => static::DEFAULT_MUST_BE_EMPTY,
        ];
    }
}
