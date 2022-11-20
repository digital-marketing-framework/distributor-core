<?php

namespace DigitalMarketingFramework\Distributor\Core\ConfigurationResolver\Evaluation;

use DigitalMarketingFramework\Core\ConfigurationResolver\Evaluation\Evaluation;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;

class GateEvaluation extends Evaluation
{
    protected const KEY_KEY = 'key';
    protected const DEFAULT_KEY = '';

    protected const KEY_PASS = 'pass';
    protected const DEFAULT_PASS = '';

    protected const KEY_KEYS_EVALUATED = 'keysEvaluated';
    
    protected const PASS_ANY = 'any';
    protected const PASS_ALL = 'all';

    public const MESSAGE_LOOP_DETECTED = 'Gate dependency loop found for key %s!';

    protected function getRoutePassCount(string $routeName): ?int
    {
        $configuration = $this->context['configuration'] ?? null;
        if ($configuration instanceof SubmissionConfigurationInterface) {
            return $configuration->getRoutePassCount($routeName);
        }
        return null;
    }

    protected function getRoutePassConfiguration(string $routeName, int $pass): ?array
    {
        $configuration = $this->context['configuration'] ?? null;
        if ($configuration instanceof SubmissionConfigurationInterface) {
            return $configuration->getRoutePassConfiguration($routeName, $pass);
        }
        return null;
    }

    /*
     * # case 1: multiple keys, no passes
     *
     * gate = routeA,routeB
     * =>
     * or {
     *     1.gate {
     *         key = a
     *         pass = any
     *     }
     *     2.gate {
     *         key = b
     *         pass = any
     *     }
     * }
     */
    protected function evaluateMultipleRoutes(): bool
    {
        $keys = GeneralUtility::castValueToArray($this->configuration);
        $gateConfig = ['or' => []];
        foreach ($keys as $key) {
            $gateConfig['or'][] = [
                RouteInterface::KEY_GATE => [
                    static::KEY_KEY => $key, 
                    static::KEY_PASS => static::PASS_ANY,
                ],
            ];
        }
        return $this->evaluate($gateConfig);
    }

    /*
     * # case 2: one key, indirect passes (any|all)
     *
     * gate { key=routeA, pass=any|all }
     * =>
     * or|and {
     *     1.gate { key=routeA, pass=0 }
     *     2.gate { key=routeA, pass=1 }
     *     # ...
     *     n.gate { key=routeA, pass=n }
     * }
     */
    protected function evaluateMultiplePasses(): bool
    {
        $key = $this->getConfig(static::KEY_KEY);
        $count = $this->getRoutePassCount($key);

        if ($count === null) {
            return false;
        }
        
        $gateConfigs = [];
        for ($i = 0; $i < $count; $i++) {
            $gateConfigs[] = [RouteInterface::KEY_GATE => [static::KEY_KEY => $key, static::KEY_PASS => $i]];
        }
        $pass = $this->getConfig(static::KEY_PASS);
        return $this->evaluate([$pass === static::PASS_ANY ? 'or' : 'and' => $gateConfigs]);
    }

    /*
     * # case 3: one key, one pass
     * gate { key=routeA, pass=n }
     * =>
     * actual evaluation of extension gate
     */
    protected function evaluateSinglePass(): bool
    {
        $key = $this->getConfig(static::KEY_KEY);
        $pass = $this->getConfig(static::KEY_PASS);
        $hash = $key . '.' . $pass;
        
        if (isset($this->context[static::KEY_KEYS_EVALUATED][$hash])) {
            // loop found, no evaluation possible
            $this->logger->error(sprintf(static::MESSAGE_LOOP_DETECTED, $hash));
            return false;
        }

        $this->context[static::KEY_KEYS_EVALUATED][$hash] = true;

        $settings = $this->getRoutePassConfiguration($key, $pass);
        if ($settings === null) {
            return false;
        }

        $result = true;
        if (!isset($settings[RouteInterface::KEY_ENABLED]) || !$settings[RouteInterface::KEY_ENABLED]) {
            $result = false;
        } elseif (isset($settings[RouteInterface::KEY_GATE]) && !empty($settings[RouteInterface::KEY_GATE])) {
            $result = $this->evaluate($settings[RouteInterface::KEY_GATE]);
        } else {
            // no gate is an automatic pass
            $result = true;
        }

        // TODO is it necessary to remove the context value again? the parent context should not be affected anyway
        //      maybe write a test like { 1.key = foo, 2.somethingThatWouldBeUsingContextKey }
        unset($this->context[static::KEY_KEYS_EVALUATED][$hash]);
        
        return $result;
    }

    public function eval(): bool
    {
        if (!isset($this->context[static::KEY_KEYS_EVALUATED])) {
            $this->context[static::KEY_KEYS_EVALUATED] = [];
        }
        
        if (!is_array($this->configuration)) {
            return $this->evaluateMultipleRoutes();
        }

        $pass = $this->getConfig(static::KEY_PASS);
        if ($pass === static::PASS_ANY || $pass === static::PASS_ALL) {
            return $this->evaluateMultiplePasses();
        }

        return $this->evaluateSinglePass();
    }

    public static function getDefaultConfiguration(): array
    {
        return parent::getDefaultConfiguration() + [
            static::KEY_KEY => static::DEFAULT_KEY,
            static::KEY_PASS => static::DEFAULT_PASS,
        ];
    }
}
