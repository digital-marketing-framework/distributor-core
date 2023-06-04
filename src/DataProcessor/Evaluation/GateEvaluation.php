<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProcessor\Evaluation;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Core\DataProcessor\Evaluation\Evaluation;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Configuration\ConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfiguration;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;

class GateEvaluation extends Evaluation
{
    public const KEY_KEY = 'key';
    public const DEFAULT_KEY = '';

    public const KEY_PASS = 'pass';
    public const DEFAULT_PASS = 'any';

    public const KEY_KEYS_EVALUATED = 'keysEvaluated';

    public const PASS_ANY = 'any';
    public const PASS_ALL = 'all';

    public const MESSAGE_LOOP_DETECTED = 'Gate dependency loop found for key %s!';

    protected function getRoutePassCount(string $routeName): ?int
    {
        $configuration = $this->context['configuration'] ?? null;
        if ($configuration instanceof ConfigurationInterface) {
            $configuration = SubmissionConfiguration::convert($configuration);
            return $configuration->getRoutePassCount($routeName);
        }
        return null;
    }

    protected function getRoutePassConfiguration(string $routeName, int $pass): ?array
    {
        $configuration = $this->context['configuration'] ?? null;
        if ($configuration instanceof ConfigurationInterface) {
            $configuration = SubmissionConfiguration::convert($configuration);
            return $configuration->getRoutePassConfiguration($routeName, $pass);
        }
        return null;
    }

    protected function evaluateSinglePass(string $key, string $pass): bool
    {
        $hash = $key . '.' . $pass;
        if (isset($this->context[static::KEY_KEYS_EVALUATED][$hash])) {
            // loop found, no evaluation possible
            throw new DigitalMarketingFrameworkException(sprintf(static::MESSAGE_LOOP_DETECTED, $hash));
        }
        $this->context[static::KEY_KEYS_EVALUATED][$hash] = true;
        $settings = $this->getRoutePassConfiguration($key, $pass);
        if ($settings === null) {
            return false;
        }

        $enabled = $settings[RouteInterface::KEY_ENABLED] ?? RouteInterface::DEFAULT_ENABLED;
        $gate = $settings[RouteInterface::KEY_GATE] ?? RouteInterface::DEFAULT_GATE;

        $result = true;
        if (!$enabled) {
            $result = false;
        } elseif (empty($gate)) {
            // no gate is an automatic pass
            $result = true;
        } else {
            $result = $this->dataProcessor->processEvaluation($gate, $this->context->copy());
        }

        // TODO is it necessary to remove the context value again? the parent context should not be affected anyway
        //      maybe write a test like { 1.key = foo, 2.somethingThatWouldBeUsingContextKey }
        unset($this->context[static::KEY_KEYS_EVALUATED][$hash]);

        return $result;
    }

    public function evaluate(): bool
    {
        if (!isset($this->context[static::KEY_KEYS_EVALUATED])) {
            $this->context[static::KEY_KEYS_EVALUATED] = [];
        }

        $key = $this->getConfig(static::KEY_KEY);
        $pass = $this->getConfig(static::KEY_PASS);
        $passCount = $this->getRoutePassCount($key);

        if ($passCount === null) {
            return false;
        }

        if ($pass === static::PASS_ANY) {
            for ($i = 0; $i < $passCount; $i++) {
                if ($this->evaluateSinglePass($key, $i)) {
                    return true;
                }
            }
            return false;
        } elseif ($pass === static::PASS_ALL) {
            for ($i = 0; $i < $passCount; $i++) {
                if (!$this->evaluateSinglePass($key, $i)) {
                    return false;
                }
            }
            return true;
        } else {
            if (!is_numeric($pass) ||  $pass < 0 || $pass >= $passCount) {
                return false;
            }
            return $this->evaluateSinglePass($key, $pass);
        }
    }

    public static function getSchema(): SchemaInterface
    {
        $schema = new ContainerSchema();
        $schema->addProperty(static::KEY_KEY, new StringSchema(static::DEFAULT_KEY));
        $schema->addProperty(static::KEY_PASS, new StringSchema(static::DEFAULT_PASS));
        return $schema;
    }
}
