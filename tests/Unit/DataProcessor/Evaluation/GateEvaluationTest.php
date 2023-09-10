<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProcessor\Evaluation;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Tests\Unit\DataProcessor\Evaluation\EvaluationTest;
use DigitalMarketingFramework\Distributor\Core\DataProcessor\Evaluation\GateEvaluation;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfiguration;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;

class GateEvaluationTest extends EvaluationTest
{
    protected const CLASS_NAME = GateEvaluation::class;
    protected const KEYWORD = 'gate';

    protected function setupDataProcessor(): void
    {
        $this->dataProcessor->method('processEvaluation')->willReturnCallback(function(array $config) {
            return $config['mockedResult'] ?? false;
        });
    }

    protected function addGate(string $routeName, string $routeId, bool $succeeds): void
    {
        $this->configuration[0][SubmissionConfiguration::KEY_DISTRIBUTOR][SubmissionConfiguration::KEY_ROUTES][$routeId] = static::createListItem([
            'type' => $routeName,
            'config' => [
                $routeName => [
                    RouteInterface::KEY_ENABLED => $succeeds,
                ],
            ],
        ], $routeId);
        $gatedRouteId = 'gated' . ucfirst($routeId);
        $this->configuration[0][SubmissionConfiguration::KEY_DISTRIBUTOR][SubmissionConfiguration::KEY_ROUTES][$gatedRouteId] = static::createListItem([
            'type' => $routeName,
            'config' => [
                $routeName => [
                    RouteInterface::KEY_ENABLED => true,
                    RouteInterface::KEY_GATE => ['mockedResult' => $succeeds]
                ],
            ],
        ], $gatedRouteId);
    }

    protected function processGateEvaluation(string $routeId, ?bool $expectedResult): void
    {
        $config = [
            GateEvaluation::KEY_ROUTE_ID => $routeId,
        ];
        $result = $this->processEvaluation($config);
        if ($expectedResult !== null) {
            $this->assertEquals($expectedResult, $result);
        }
    }

    protected function processGateEvaluationWithBothEnabledAndGateConfig(string $routeId, bool $expectedResult): void
    {
        $this->processGateEvaluation($routeId, $expectedResult);
        $this->processGateEvaluation('gated' . ucfirst($routeId), $expectedResult);
    }

    /** @test */
    public function gatePasses(): void
    {
        $this->setupDataProcessor();
        $this->addGate('route1', 'routeId1', true);
        $this->processGateEvaluationWithBothEnabledAndGateConfig('routeId1', true);
    }

    /** @test */
    public function gateDoesNotPass(): void
    {
        $this->setupDataProcessor();
        $this->addGate('route1', 'routeId1', false);
        $this->processGateEvaluationWithBothEnabledAndGateConfig('routeId1', false);
    }

    /** @test */
    public function gateDoesNotExist(): void
    {
        $this->setupDataProcessor();
        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->processGateEvaluation('routeId1', false);
    }

    /** @test */
    public function gateLoopContextApplied(): void
    {
        $this->dataProcessor->expects($this->exactly(1))->method('processEvaluation')->willReturnCallback(function(array $config, $context) {
            $this->assertTrue($context[GateEvaluation::KEY_ROUTE_IDS_EVALUATED]['gatedRouteId1']);
            return $config['mockedResult'] ?? false;
        });
        $this->addGate('route1', 'routeId1', true);
        $this->processGateEvaluation('gatedRouteId1', null);
    }

    /** @test */
    public function gateLoopIsDetected(): void
    {
        $this->setupDataProcessor();
        $this->addGate('route1', 'routeId1', true);
        $context = $this->getContext();
        $context[GateEvaluation::KEY_ROUTE_IDS_EVALUATED]['gatedRouteId1'] = true;
        $config = [
            GateEvaluation::KEY_ROUTE_ID => 'gatedRouteId1',
        ];
        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->processEvaluation($config, $context);
    }
}
