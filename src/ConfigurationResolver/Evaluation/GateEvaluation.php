<?php

namespace DigitalMarketingFramework\Distributer\Core\ConfigurationResolver\Evaluation;

use DigitalMarketingFramework\Core\ConfigurationResolver\Evaluation\Evaluation;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;

class GateEvaluation extends Evaluation
{
    public const MESSAGE_LOOP_DETECTED = 'Gate dependency loop found for key %s!';

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
    protected function evaluateMultipleRoutes()
    {
        $keys = GeneralUtility::castValueToArray($this->configuration);
        $gateConfig = ['or' => []];
        foreach ($keys as $key) {
            $gateConfig['or'][] = ['gate' => ['key' => $key, 'pass' => 'any']];
        }
        /** @var EvaluationInterface $evaluation */
        $evaluation = $this->resolveKeyword('general', $gateConfig);
        return $evaluation->eval();
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
    protected function evaluateMultiplePasses()
    {
        $key = $this->configuration['key'];
        $gateConfigs = [];
        $count = $this->context['configuration']->getRoutePassCount($key);
        for ($i = 0; $i < $count; $i++) {
            $gateConfigs[] = ['gate' => ['key' => $key, 'pass' => $i]];
        }
        /** @var EvaluationInterface $evaluation */
        $evaluation = $this->resolveKeyword('general', [$this->configuration['pass'] === 'any' ? 'or' : 'and' => $gateConfigs]);
        return $evaluation->eval();
    }

    /*
     * # case 3: one key, one pass
     * gate { key=routeA, pass=n }
     * =>
     * actual evaluation of extension gate
     */
    protected function evaluateSinglePass()
    {
        $result = true;
        $key = $this->configuration['key'];
        $pass = $this->configuration['pass'];
        if (isset($this->context['keysEvaluated'][$key . '.' . $pass])) {
            // loop found, no evaluation possible
            $this->logger->error(sprintf(static::MESSAGE_LOOP_DETECTED, $key . '.' . $pass));
            $result = false;
        } else {
            $this->context['keysEvaluated'][$key . '.' . $pass] = true;
            
            $settings = $this->context['configuration']->getRoutePassConfiguration($key, $pass);
            if (!isset($settings['enabled']) || !$settings['enabled']) {
                $result = false;
            } elseif (isset($settings['gate']) && !empty($settings['gate'])) {
                /** @var EvaluationInterface $evaluation */
                $evaluation = $this->resolveKeyword('general', $settings['gate']);
                $result = $evaluation->eval();
            } else {
                // no gate is an automatic pass
                $result = true;
            }

            unset($this->context['keysEvaluated'][$key . '.' . $pass]);
        }
        return $result;
    }

    public function eval(): bool
    {
        if (!isset($this->context['keysEvaluated'])) {
            $this->context['keysEvaluated'] = [];
        }
        
        if (!is_array($this->configuration)) {
            return $this->evaluateMultipleRoutes();
        }

        if ($this->configuration['pass'] === 'any' || $this->configuration['pass'] === 'all') {
            return $this->evaluateMultiplePasses();
        }

        return $this->evaluateSinglePass();
    }
}
