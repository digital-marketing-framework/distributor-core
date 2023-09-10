<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration\DataProcessor\Evaluation;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Tests\Integration\DataProcessor\Evaluation\EvaluationTest;
use DigitalMarketingFramework\Distributor\Core\DataProcessor\Evaluation\GateEvaluation;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfiguration;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;
use DigitalMarketingFramework\Distributor\Core\Tests\Integration\DataProcessor\DataProcessorPluginTestTrait;

class GateEvaluationTest extends EvaluationTest
{
    use DataProcessorPluginTestTrait;

    protected const KEYWORD = 'gate';

    public function setUp(): void
    {
        parent::setUp();

        // foreign gate that passes
        $this->addSimpleGate('routeThatPasses', 'routeIdThatPasses', true);

        // foreign gate that does not pass
        $this->addSimpleGate('routeThatDoesNotPass', 'routeIdThatDoesNotPass', false);

        // direct loop gate
        $this->addGateConfig('routeLoopA1', 'routeIdLoopA1', true, [
            'type' => 'gate',
            'config' => [
                'gate' => [
                    GateEvaluation::KEY_ROUTE_ID => 'routeIdLoopA2',
                ]
            ],
        ]);
        $this->addGateConfig('routeLoopA2', 'routeIdLoopA2', true, [
            'type' => 'gate',
            'config' => [
                'gate' => [
                    GateEvaluation::KEY_ROUTE_ID => 'routeIdLoopA1',
                ]
            ],
        ]);

        // indirect loop gate
        $this->addGateConfig('routeLoopB1', 'routeIdLoopB1', true, [
            'type' => 'gate',
            'config' => [
                'gate' => [
                    GateEvaluation::KEY_ROUTE_ID => 'routeIdLoopB2',
                ]
            ],
        ]);
        $this->addGateConfig('routeLoopB2', 'routeIdLoopB2', true, [
            'type' => 'gate',
            'config' => [
                'gate' => [
                    GateEvaluation::KEY_ROUTE_ID => 'routeIdLoopB3',
                ]
            ],
        ]);
        $this->addGateConfig('routeLoopB3', 'routeIdLoopB3', true, [
            'type' => 'gate',
            'config' => [
                'gate' => [
                    GateEvaluation::KEY_ROUTE_ID => 'routeIdLoopB1',
                ]
            ],
        ]);
    }

    protected function addGateConfig(string $routeName, string $routeId, bool $enabled, array $gateConfig = []): void
    {
        $this->configuration[0][SubmissionConfiguration::KEY_DISTRIBUTOR][SubmissionConfiguration::KEY_ROUTES][$routeId] = static::createListItem([
            'type' => $routeName,
            'config' => [
                $routeName => [
                    RouteInterface::KEY_ENABLED => $enabled,
                    RouteInterface::KEY_GATE => $gateConfig,
                ],
            ],
        ], $routeId);
    }

    protected function addSimpleGate(string $routeName, string $routeId, bool $succeeds): void
    {
        $this->addGateConfig($routeName, $routeId, $succeeds);
        $gateType = $succeeds ? 'true' : 'false';
        $this->addGateConfig($routeName, 'gated' . ucfirst($routeId), true, [
            'type' => $gateType,
            'config' => [
                $gateType => [],
            ],
        ]);
    }

    public function falseTrueProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider falseTrueProvider
     * @test
     */
    public function gatePasses(bool $useGate): void
    {
        $routeId = $useGate ? 'gatedRouteIdThatPasses' : 'routeIdThatPasses';
        $config = [
            GateEvaluation::KEY_ROUTE_ID => $routeId,
        ];
        $result = $this->processEvaluation($config);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider falseTrueProvider
     * @test
     */
    public function gateDoesNotPass(bool $useGate): void
    {
        $routeId = $useGate ? 'gatedRouteIdThatDoesNotPass' : 'routeIdThatDoesNotPass';
        $config = [
            GateEvaluation::KEY_ROUTE_ID => $routeId,
        ];
        $result = $this->processEvaluation($config);
        $this->assertFalse($result);
    }

    /** @test */
    public function gateDoesNotExist(): void
    {
        $config = [
            GateEvaluation::KEY_ROUTE_ID => 'routeIdThatDoesNotExist',
        ];
        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->processEvaluation($config);
    }

    /** @test */
    public function directLoopGetsDetected(): void
    {
        $config = [
            GateEvaluation::KEY_ROUTE_ID => 'routeLoopA1',
        ];
        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->processEvaluation($config);
    }

    /** @test */
    public function indirectLoopGetsDetected(): void
    {
        $config = [
            GateEvaluation::KEY_ROUTE_ID => 'routeLoopB1',
        ];
        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->processEvaluation($config);
    }
}
