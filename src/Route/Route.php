<?php

namespace DigitalMarketingFramework\Distributor\Core\Route;

use DigitalMarketingFramework\Core\ConfigurationResolver\Context\ConfigurationResolverContext;
use DigitalMarketingFramework\Core\ConfigurationResolver\Context\ConfigurationResolverContextInterface;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Helper\ConfigurationResolverTrait;
use DigitalMarketingFramework\Core\Helper\ConfigurationTrait;
use DigitalMarketingFramework\Core\Model\Data\Data;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Service\DataProcessor;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Plugin\Plugin;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Service\RelayInterface;

abstract class Route extends Plugin implements RouteInterface
{
    use ConfigurationTrait;
    use ConfigurationResolverTrait;

    protected const DEFAULT_ASYNC = null;
    protected const DEFAULT_DISABLE_STORAGE = null;

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

    protected function getConfigurationResolverContext(): ConfigurationResolverContextInterface
    {
        return new ConfigurationResolverContext(
            $this->submission->getData(),
            ['configuration' => $this->submission->getConfiguration()]
        );
    }

    protected function buildData(): DataInterface
    {
        $data = $this->resolveContent([
            'dataMap' => $this->getConfig(static::KEY_DATA),
        ]);
        if (!$data instanceof DataInterface) {
            $data = new Data();
        }
        return $data;
    }

    protected function processGate(): bool
    {
        return $this->enabled()
            && $this->evaluate($this->getConfig(static::KEY_GATE));
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
        return $this->getConfig(RelayInterface::KEY_ASYNC);
    }

    public function disableStorage(): ?bool
    {
        return $this->getConfig(RelayInterface::KEY_DISABLE_STORAGE);
    }

    public function getEnabledDataProviders(): array
    {
        return GeneralUtility::castValueToArray(
            $this->resolveContent(
                $this->getConfig(static::KEY_ENABLE_DATA_PROVIDERS)
            )
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
            static::KEY_GATE => static::DEFAULT_GATE,
            static::KEY_DATA => DataProcessor::getDefaultConfiguration(),
            // TODO: static::KEY_MARKETING_CONSENT => static::DEFAULT_MARKETING_CONSENT?
        ];
    }
}
