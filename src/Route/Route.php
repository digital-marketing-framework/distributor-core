<?php

namespace DigitalMarketingFramework\Distributer\Core\Route;

use DigitalMarketingFramework\Core\ConfigurationResolver\Context\ConfigurationResolverContext;
use DigitalMarketingFramework\Core\ConfigurationResolver\Context\ConfigurationResolverContextInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Helper\ConfigurationTrait;
use DigitalMarketingFramework\Core\Plugin\Plugin;
use DigitalMarketingFramework\Core\Request\RequestInterface;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributer\Core\ConfigurationResolver\ContentResolver\GeneralContentResolver;
use DigitalMarketingFramework\Distributer\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributer\Core\Registry\RegistryInterface;

abstract class Route extends Plugin implements RouteInterface
{
    use ConfigurationTrait;

    protected const KEY_ENABLED = 'enabled';
    protected const DEFAULT_ENABLED = false;

    protected const KEY_IGNORE_EMPTY_FIELDS = 'ignoreEmptyFields';
    protected const DEFAULT_IGNORE_EMPTY_FIELDS = false;

    protected const KEY_PASSTHROUGH_FIELDS = 'passthroughFields';
    protected const DEFAULT_PASSTHROUGH_FIELDS = false;

    protected const KEY_EXCLUDE_FIELDS = 'excludeFields';
    protected const DEFAULT_EXCLUDE_FIELDS = [];

    protected const KEY_GATE = 'gate';
    protected const DEFAULT_GATE = [];

    protected const KEY_FIELDS = 'fields';
    protected const DEFAULT_FIELDS = [];

    protected SubmissionDataSetInterface $submission;

    protected int $pass;

    public function __construct(
        string $keyword, 
        protected RegistryInterface $registry
    ) {
        parent::__construct($keyword);
    }

    protected function getConfigurationResolverContext(): ConfigurationResolverContextInterface
    {
        return new ConfigurationResolverContext(
            $this->submission->getData(),
            [
                'configuration' => $this->submission->getConfiguration(),
            ]
        );
    }

    protected function resolveContent($config, $context = null)
    {
        if ($context === null) {
            $context = $this->getConfigurationResolverContext();
        }
        /** @var GeneralContentResolver $contentResolver */
        $contentResolver = $this->registry->getContentResolver('general', $config, $context);
        return $contentResolver->resolve();
    }

    protected function buildRouteData(): array
    {
        $fields = [];
        if ($this->getConfig(static::KEY_PASSTHROUGH_FIELDS)) {
            // pass through all fields as they are
            foreach ($this->submission->getData() as $key => $value) {
                $fields[$key] = $value;
            }
        } else {
            // compute field configuration
            $fieldConfigList = $this->getConfig(static::KEY_FIELDS);
            $baseContext = $this->getConfigurationResolverContext();
            foreach ($fieldConfigList as $key => $value) {
                $result = $this->resolveContent($value, $baseContext->copy());
                if ($result !== null) {
                    $fields[$key] = $result;
                }
            }
        }

        // ignore empty fields
        if ($this->getConfig(static::KEY_IGNORE_EMPTY_FIELDS)) {
            $fields = array_filter($fields, function ($a) { return !GeneralUtility::isEmpty($a); });
        }

        // exclude specific fields directly
        $excludeFields = $this->getConfig(static::KEY_EXCLUDE_FIELDS);
        GeneralUtility::castValueToArray($excludeFields);
        foreach ($excludeFields as $excludeField) {
            if (array_key_exists($excludeField, $fields)) {
                unset($fields[$excludeField]);
            }
        }

        return $fields;
    }

    protected function processGate(): bool
    {
        $context = $this->getConfigurationResolverContext();
        $evaluation = $this->registry->getEvaluation(
            'gate',
            [
                'key' => $this->getKeyword(),
                'pass' => $this->pass
            ],
            $context
        );
        return $evaluation->eval();
    }

    public function processPass(SubmissionDataSetInterface $submission, int $pass): bool
    {
        $this->submission = $submission;
        $this->pass = $pass;
        $this->configuration = $submission->getConfiguration()->getRoutePassConfiguration($this->getKeyword(), $pass);

        if (!$this->processGate()) {
            $this->logger->debug('gate not passed for route "' . $this->getKeyword() . '" in pass ' . $pass . '.');
            return false;
        }
        $data = $this->buildRouteData();
        if (!$data) {
            throw new DigitalMarketingFrameworkException('no data generated for route "' . $this->getKeyword() . '" in pass ' . $pass . '.');
        }

        $dataDispatcher = $this->getDispatcher();
        if (!$dataDispatcher) {
            throw new DigitalMarketingFrameworkException('no dispatcher found for route "' . $this->getKeyword() . '" in pass ' . $pass . '.');
        }

        $dataDispatcher->send($data);
        return true;
    }

    public function getPassCount(SubmissionDataSetInterface $submission): int
    {
        return $submission->getConfiguration()->getRoutePassCount($this->getKeyword());
    }

    public function addContext(SubmissionDataSetInterface $submission, RequestInterface $request, int $pass): void
    {
        $this->submission = $submission;
        $this->pass = $pass;
        $this->configuration = $submission->getConfiguration()->getRoutePassConfiguration(static::getKeyword(), $pass);
    }

    /**
     * @return DataDispatcherInterface|null
     */
    abstract protected function getDispatcher();

    public static function getDefaultConfiguration(): array
    {
        return [
            static::KEY_ENABLED => static::DEFAULT_ENABLED,
            static::KEY_IGNORE_EMPTY_FIELDS => static::DEFAULT_IGNORE_EMPTY_FIELDS,
            static::KEY_PASSTHROUGH_FIELDS => static::DEFAULT_PASSTHROUGH_FIELDS,
            static::KEY_EXCLUDE_FIELDS => static::DEFAULT_EXCLUDE_FIELDS,
            static::KEY_GATE => static::DEFAULT_GATE,
            static::KEY_FIELDS => static::DEFAULT_FIELDS,
        ];
    }
}
