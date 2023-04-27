<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration\DataProcessor\Evaluation;

use DigitalMarketingFramework\Core\Tests\Integration\DataProcessor\Evaluation\EvaluationTest;
use DigitalMarketingFramework\Distributor\Core\DataProcessor\Evaluation\GateEvaluation;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfiguration;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;
use DigitalMarketingFramework\Distributor\Core\Tests\Integration\DataProcessorRegistryTestTrait;
use DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProcessor\Evaluation\GateEvaluationTest as GateEvaluationUnitTest;

class GateEvaluationTest extends EvaluationTest
{
    use DataProcessorRegistryTestTrait;

    protected const KEYWORD = 'gate';

    public function setUp(): void
    {
        parent::setUp();
        foreach (GateEvaluationUnitTest::GATE_CONFIGURATIONS as $gateConfiguration) {
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
            RouteInterface::KEY_GATE => $this->getEvaluationConfiguration([], $succeeds ? 'true' : 'false'),
        ];
    }

    public function gateProvider(): array
    {
        return GateEvaluationUnitTest::GATE_TEST_CASES;
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
    public function recursiveGateSucceds(): void
    {
        $this->configuration[0][SubmissionConfiguration::KEY_DISTRIBUTOR][SubmissionConfiguration::KEY_ROUTES]['recursiveGate'][SubmissionConfiguration::KEY_ROUTE_PASSES][0] = [
            RouteInterface::KEY_ENABLED => true,
            RouteInterface::KEY_GATE => $this->getEvaluationConfiguration([GateEvaluation::KEY_KEY => 'routeGateSucceeds', GateEvaluation::KEY_PASS], 'gate'),
        ];
        $config = [
            GateEvaluation::KEY_KEY => 'recursiveGate',
            GateEvaluation::KEY_PASS => 0,
        ];
        $result = $this->processEvaluation($config);
        $this->assertTrue($result);
    }

    /** @test */
    public function recursiveGateFails(): void
    {
        $this->configuration[0][SubmissionConfiguration::KEY_DISTRIBUTOR][SubmissionConfiguration::KEY_ROUTES]['recursiveGate'][SubmissionConfiguration::KEY_ROUTE_PASSES][0] = [
            RouteInterface::KEY_ENABLED => true,
            RouteInterface::KEY_GATE => $this->getEvaluationConfiguration([GateEvaluation::KEY_KEY => 'routeGateDoesNotSucceed', GateEvaluation::KEY_PASS], 'gate'),
        ];
        $config = [
            GateEvaluation::KEY_KEY => 'recursiveGate',
            GateEvaluation::KEY_PASS => 0,
        ];
        $result = $this->processEvaluation($config);
        $this->assertFalse($result);
    }

    /** @test */
    public function recursiveGateWithLoop(): void
    {
        $this->configuration[0][SubmissionConfiguration::KEY_DISTRIBUTOR][SubmissionConfiguration::KEY_ROUTES]['gate1'][SubmissionConfiguration::KEY_ROUTE_PASSES][0] = [
            RouteInterface::KEY_ENABLED => true,
            RouteInterface::KEY_GATE => $this->getEvaluationConfiguration([GateEvaluation::KEY_KEY => 'gate2', GateEvaluation::KEY_PASS], 'gate'),
        ];
        $this->configuration[0][SubmissionConfiguration::KEY_DISTRIBUTOR][SubmissionConfiguration::KEY_ROUTES]['gate2'][SubmissionConfiguration::KEY_ROUTE_PASSES][0] = [
            RouteInterface::KEY_ENABLED => true,
            RouteInterface::KEY_GATE => $this->getEvaluationConfiguration([GateEvaluation::KEY_KEY => 'gate1', GateEvaluation::KEY_PASS], 'gate'),
        ];
        $config = [
            GateEvaluation::KEY_KEY => 'gate1',
            GateEvaluation::KEY_PASS => 0,
        ];
        $this->expectExceptionMessage('Gate dependency loop found for key gate1.0!');
        $this->processEvaluation($config);
    }
}
