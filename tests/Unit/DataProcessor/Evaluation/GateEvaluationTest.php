<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProcessor\Evaluation;

use DigitalMarketingFramework\Core\Tests\Unit\DataProcessor\Evaluation\EvaluationTest;
use DigitalMarketingFramework\Distributor\Core\DataProcessor\Evaluation\GateEvaluation;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfiguration;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;

class GateEvaluationTest extends EvaluationTest
{
    protected const CLASS_NAME = GateEvaluation::class;
    protected const KEYWORD = 'gate';

    public const GATE_CONFIGURATIONS = [
        ['routeGateSucceeds', 0, true],
        ['routeGateDoesNotSucceed', 0, false],
        
        ['routeAllPassesSucceed', 0, true],
        ['routeAllPassesSucceed', 1, true],

        ['routeNoPassesSucceed', 0, false],
        ['routeNoPassesSucceed', 1, false],

        ['routeSomePassesSucceed', 0, true],
        ['routeSomePassesSucceed', 1, false],

        ['routeSomePassesSucceed2', 0, false],
        ['routeSomePassesSucceed2', 1, true],
    ];

    public const GATE_TEST_CASES = [
        // routeName, routePass, expected
        ['routeGateSucceeds',       null, true],
        ['routeGateSucceeds',       '0',   true],
        ['routeGateSucceeds',       'any', true],
        ['routeGateSucceeds',       'all', true],
        
        ['routeGateDoesNotSucceed', null, false],
        ['routeGateDoesNotSucceed', '0',   false],
        ['routeGateDoesNotSucceed', 'any', false],
        ['routeGateDoesNotSucceed', 'all', false],

        ['routeAllPassesSucceed',   null,  true],
        ['routeAllPassesSucceed',   '0',   true],
        ['routeAllPassesSucceed',   '1',   true],
        ['routeAllPassesSucceed',   'any', true],
        ['routeAllPassesSucceed',   'all', true],

        ['routeNoPassesSucceed',    null,  false],
        ['routeNoPassesSucceed',    '0',   false],
        ['routeNoPassesSucceed',    '1',   false],
        ['routeNoPassesSucceed',    'any', false],
        ['routeNoPassesSucceed',    'all', false],

        ['routeSomePassesSucceed',  null,  true],
        ['routeSomePassesSucceed',  '0',   true],
        ['routeSomePassesSucceed',  '1',   false],
        ['routeSomePassesSucceed',  'any', true],
        ['routeSomePassesSucceed',  'all', false],

        ['routeSomePassesSucceed2', null,  true],
        ['routeSomePassesSucceed2', '1',   true],
        ['routeSomePassesSucceed2', '0',   false],
        ['routeSomePassesSucceed2', 'any', true],
        ['routeSomePassesSucceed2', 'all', false],
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->dataProcessor->method('processEvaluation')->willReturnCallback(function(array $config) {
            return $config['mockedResult'] ?? false;
        });
        foreach (static::GATE_CONFIGURATIONS as $gateConfiguration) {
            $this->addGate(...$gateConfiguration);
        }
    }

    protected function addGate(string $routeName, int $pass, bool $succeeds): void
    {
        $this->configuration[0][SubmissionConfiguration::KEY_DISTRIBUTOR][SubmissionConfiguration::KEY_ROUTES][$routeName][SubmissionConfiguration::KEY_ROUTE_PASSES][$pass] = [
            RouteInterface::KEY_ENABLED => $succeeds,
        ];
        $this->configuration[0][SubmissionConfiguration::KEY_DISTRIBUTOR][SubmissionConfiguration::KEY_ROUTES]['gated' . ucfirst($routeName)][SubmissionConfiguration::KEY_ROUTE_PASSES][$pass] = [
            RouteInterface::KEY_ENABLED => true,
            RouteInterface::KEY_GATE => ['mockedResult' => $succeeds]
        ];
    }

    public function gateProvider(): array
    {
        return static::GATE_TEST_CASES;
    }

    protected function runGate(string $routeName, int|string|null $pass, bool $expectedResult): void
    {
        $config = [
            GateEvaluation::KEY_KEY => $routeName, 
        ];
        if ($pass !== null) {
            $config[GateEvaluation::KEY_PASS] = $pass;
        }
        $result = $this->processEvaluation($config);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @test
     * @dataProvider gateProvider
     */
    public function gate(string $routeName, int|string|null $pass, bool $expectedResult): void
    {
        $this->runGate($routeName, $pass, $expectedResult);
        $this->runGate('gated' . ucfirst($routeName), $pass, $expectedResult);
    }

    /** @test */
    public function recursiveLoopWillBeDetected(): void
    {
        $context = $this->getContext();
        $context[GateEvaluation::KEY_KEYS_EVALUATED] = ['routeName1.0' => true];
        $this->expectExceptionMessage('Gate dependency loop found for key routeName1.0!');
        $this->processEvaluation([GateEvaluation::KEY_KEY => 'routeName1', GateEvaluation::KEY_PASS => '0'], $context);
    }
}
