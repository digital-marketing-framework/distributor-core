<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration\ConfigurationResolver\Evaluation;

use DigitalMarketingFramework\Core\Tests\Integration\ConfigurationResolver\Evaluation\AbstractEvaluationTest;
use DigitalMarketingFramework\Distributor\Core\ConfigurationResolver\Evaluation\GateEvaluation;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfiguration;
use DigitalMarketingFramework\Distributor\Core\Tests\Integration\ConfigurationResolverRegistryTestTrait;

/**
 * @covers GateEvaluation
 */
class GateEvaluationTest extends AbstractEvaluationTest
{
    use ConfigurationResolverRegistryTestTrait;

    protected array $submissionConfiguration = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->data = [
            'field1' => 'value1', 
            'field2' => 'value2', 
            'field3' => 'value3',
        ];
        $this->submissionConfiguration = [];
    }

    protected function runResolverProcess(mixed $config): mixed
    {
        $this->configurationResolverContext['configuration'] = new SubmissionConfiguration($this->submissionConfiguration);
        return parent::runResolverProcess($config);
    }

    protected function runEvaluationProcess(mixed $config): bool
    {
        $this->configurationResolverContext['configuration'] = new SubmissionConfiguration($this->submissionConfiguration);
        return parent::runEvaluationProcess($config);
    }

    protected function setupSimpleRoutes(): void
    {
        $this->createRouteConfig('routeGateSucceeds', true);
        $this->createRouteConfig('routeGateSucceeds2', true);
        $this->createRouteConfig('routeGateDoesNotSucceed', false);
        $this->createRouteConfig('routeGateDoesNotSucceed2', false);
        $this->createRouteConfig('routeAllPassesSucceed', [true, true]);
        $this->createRouteConfig('routeNoPassesSucceed', [false, false]);
        $this->createRouteConfig('routeSomePassesSucceed', [true, false]);
        $this->createRouteConfig('routeSomePassesSucceed2', [false, true]);
    }

    protected function createGateConfig($passes): array
    {
        return [
            'field1' => $passes ? 'value1' : 'value2',
        ];
    }

    protected function createRouteConfig($name, $gatePasses): void
    {
        $routeConf = [
            'distributor' => [
                'routes' => [
                    $name => [
                        'enabled' => true,
                    ]
                ],
            ],
        ];
        if (is_array($gatePasses)) {
            $routeConf['distributor']['routes'][$name]['passes'] = [];
            foreach ($gatePasses as $pass => $passGatePasses) {
                $routeConf['distributor']['routes'][$name]['passes'][$pass] = [
                    'gate' => $this->createGateConfig($passGatePasses),
                ];
            }
        } else {
            $routeConf['distributor']['routes'][$name]['gate'] = $this->createGateConfig($gatePasses);
        }
        $this->submissionConfiguration[] = $routeConf;
    }

    public function gateProvider(): array
    {
        return [
            // routeName, routePass, expected

            // routes without passes
            ['routeGateSucceeds',                                null, true],
            ['routeGateSucceeds',                               '0',   true],
            ['routeGateSucceeds',                               'any', true],
            ['routeGateSucceeds',                               'all', true],
            ['routeGateDoesNotSucceed',                          null, false],
            ['routeGateDoesNotSucceed',                         '0',   false],
            ['routeGateDoesNotSucceed',                         'any', false],
            ['routeGateDoesNotSucceed',                         'all', false],

            ['routeGateSucceeds,routeGateSucceeds2',             null, true],
            ['routeGateSucceeds,routeGateDoesNotSucceed',        null, true],
            ['routeGateDoesNotSucceed,routeGateDoesNotSucceed2', null, false],

            // routes with passes
            ['routeAllPassesSucceed',                           null,  true],
            ['routeAllPassesSucceed',                           '0',   true],
            ['routeAllPassesSucceed',                           '1',   true],
            ['routeAllPassesSucceed',                           'any', true],
            ['routeAllPassesSucceed',                           'all', true],

            ['routeNoPassesSucceed',                            null,  false],
            ['routeNoPassesSucceed',                            '0',   false],
            ['routeNoPassesSucceed',                            '1',   false],
            ['routeNoPassesSucceed',                            'any', false],
            ['routeNoPassesSucceed',                            'all', false],

            ['routeSomePassesSucceed',                          null,  true],
            ['routeSomePassesSucceed',                          '0',   true],
            ['routeSomePassesSucceed',                          '1',   false],
            ['routeSomePassesSucceed',                          'any', true],
            ['routeSomePassesSucceed',                          'all', false],

            ['routeSomePassesSucceed2',                         null,  true],
            ['routeSomePassesSucceed2',                         '1',   true],
            ['routeSomePassesSucceed2',                         '0',   false],
            ['routeSomePassesSucceed2',                         'any', true],
            ['routeSomePassesSucceed2',                         'all', false],

            ['routeAllPassesSucceed,routeNoPassesSucceed',      null,  true],
            ['routeAllPassesSucceed,routeSomePassesSucceed',    null,  true],
            ['routeNoPassesSucceed,routeSomePassesSucceed',     null,  true],
            ['routeAllPassesSucceed,routeSomePassesSucceed,routeNoPassesSucceed', null,  true],

            // mixed routes with and without passes
            ['routeGateSucceeds,routeAllPassesSucceed',         null,  true],
            ['routeGateSucceeds,routeSomePassesSucceed',        null,  true],
            ['routeGateSucceeds,routeNoPassesSucceed',          null,  true],

            ['routeGateDoesNotSucceed,routeAllPassesSucceed',   null,  true],
            ['routeGateDoesNotSucceed,routeSomePassesSucceed',  null,  true],
            ['routeGateDoesNotSucceed,routeNoPassesSucceed',    null,  false],
        ];
    }

    /**
     * @param $routeName
     * @param $routePass
     * @param $expected
     * @dataProvider gateProvider
     * @test
     */
    public function gate($routeName, $routePass, $expected): void
    {
        $this->setupSimpleRoutes();
        $config = [];
        if ($routePass === null) {
            $config['gate'] = $routeName;
        } else {
            $config['gate'] = [
                'key' => $routeName,
                'pass' => $routePass,
            ];
        }

        $this->logger->expects($this->never())->method('error');

        $result = $this->runEvaluationProcess($config);
        if ($expected) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    /** @test */
    public function recursiveGateEvaluationSucceeds(): void
    {
        $this->submissionConfiguration[] = [
            'distributor' => [
                'routes' => [
                    'route1' => [
                        'enabled' => true,
                        'gate' => [
                            'gate' => 'route2',
                        ],
                    ],
                    'route2' => [
                        'enabled' => true,
                    ],
                ],
            ],
        ];
        $config = [
            'gate' => 'route1',
        ];

        $this->logger->expects($this->never())->method('error');

        $result = $this->runEvaluationProcess($config);
        $this->assertTrue($result);
    }

    /** @test */
    public function recursiveGateEvaluationFails(): void
    {
        $this->submissionConfiguration[] = [
            'distributor' => [
                'routes' => [
                    'route1' => [
                        'enabled' => true,
                        'gate' => [
                            'gate' => 'route2',
                        ],
                    ],
                    'route2' => [
                        'enabled' => false,
                    ],
                ],
            ],
        ];
        $config = [
            'gate' => 'route1',
        ];

        $this->logger->expects($this->never())->method('error');

        $result = $this->runEvaluationProcess($config);
        $this->assertFalse($result);
    }

    /** @test */
    public function deepRecursiveGateEvaluationWithoutLoopSucceeds(): void
    {
        $this->submissionConfiguration[] = [
            'distributor' => [
                'routes' => [
                    'route1' => [
                        'enabled' => true,
                        'gate' => [
                            'gate' => 'route2',
                        ],
                    ],
                    'route2' => [
                        'enabled' => true,
                        'gate' => [
                            'gate' => 'route3',
                        ],
                    ],
                    'route3' => [
                        'enabled' => true,
                    ],
                ],
            ],
        ];
        $config = [
            'gate' => 'route1',
        ];

        $this->logger->expects($this->never())->method('error');

        $result = $this->runEvaluationProcess($config);
        $this->assertTrue($result);
    }

    /** @test */
    public function deepRecursiveGateEvaluationWithLoopFailsAndLogsError(): void
    {
        $this->submissionConfiguration[] = [
            'distributor' => [
                'routes' => [
                    'route1' => [
                        'enabled' => true,
                        'gate' => [
                            'gate' => 'route2',
                        ],
                    ],
                    'route2' => [
                        'enabled' => true,
                        'gate' => [
                            'gate' => 'route3',
                        ],
                    ],
                    'route3' => [
                        'enabled' => true,
                        'gate' => [
                            'gate' => 'route1',
                        ],
                    ],
                ],
            ],
        ];
        $config = [
            'gate' => 'route1',
        ];

        $this->logger->expects($this->once())->method('error')->with(sprintf(GateEvaluation::MESSAGE_LOOP_DETECTED, 'route1.0'));

        $result = $this->runEvaluationProcess($config);
        $this->assertFalse($result);
    }

    /** @test */
    public function selfReferenceCountsAsLoopThusFailsAndLogsError(): void
    {
        $this->submissionConfiguration[] = [
            'distributor' => [
                'routes' => [
                    'route1' => [
                        'enabled' => true,
                        'gate' => [
                            'gate' => 'route1',
                        ],
                    ],
                ],
            ],
        ];
        $config = [
            'gate' => 'route1',
        ];

        $this->logger->expects($this->once())->method('error')->with(sprintf(GateEvaluation::MESSAGE_LOOP_DETECTED, 'route1.0'));

        $result = $this->runEvaluationProcess($config);
        $this->assertFalse($result);
    }

    /** @test */
    public function selfReferencedPassCountsAsLoopThusFailsAndLogsError(): void
    {
        $this->submissionConfiguration[] = [
            'distributor' => [
                'routes' => [
                    'route1' => [
                        'enabled' => true,
                        'passes' => [
                            [
                                'gate' => [
                                    'gate' => [
                                        'key' => 'route1',
                                        'pass' => 0,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $config = [
            'gate' => [
                'key' => 'route1',
                'pass' => '0',
            ],
        ];

        $this->logger->expects($this->once())->method('error')->with(sprintf(GateEvaluation::MESSAGE_LOOP_DETECTED, 'route1.0'));

        $result = $this->runEvaluationProcess($config);
        $this->assertFalse($result);
    }

    /** @test */
    public function passLoopWithinOneRouteFailsAndLogsError(): void
    {
        $this->submissionConfiguration[] = [
            'distributor' => [
                'routes' => [
                    'route1' => [
                        'enabled' => true,
                        'passes' => [
                            [
                                'gate' => [
                                    'gate' => [
                                        'key' => 'route1',
                                        'pass' => 1,
                                    ],
                                ],
                            ],
                            [
                                'gate' => [
                                    'gate' => [
                                        'key' => 'route1',
                                        'pass' => 0,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $config = [
            'gate' => [
                'key' => 'route1',
                'pass' => '1',
            ],
        ];

        $this->logger->expects($this->once())->method('error')->with(sprintf(GateEvaluation::MESSAGE_LOOP_DETECTED, 'route1.1'));

        $result = $this->runEvaluationProcess($config);
        $this->assertFalse($result);
    }

    /** @test */
    public function passDependencyWithinOneRouteWithoutLoopSucceeds(): void
    {
        $this->submissionConfiguration[] = [
            'distributor' => [
                'routes' => [
                    'route1' => [
                        'passes' => [
                            [
                                'enabled' => true,
                                'gate' => [
                                    'gate' => [
                                        'key' => 'route1',
                                        'pass' => 1,
                                    ],
                                ],
                            ],
                            [
                                'enabled' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $config = [
            'gate' => [
                'key' => 'route1',
                'pass' => '0',
            ],
        ];

        $this->logger->expects($this->never())->method('error');

        $result = $this->runEvaluationProcess($config);
        $this->assertTrue($result);
    }

    /** @test */
    public function twoSeparateEvaluationsWithSameGateDoNotCountAsLoopAndSucceed(): void
    {
        $this->submissionConfiguration[] = [
            'distributor' => [
                'routes' => [
                    'route1' => [
                        'enabled' => true,
                        'gate' => [
                            'gate' => 'route2',
                        ],
                    ],
                    'route2' => [
                        'enabled' => true,
                    ],
                ],
            ],
        ];
        $config = [
            ['gate' => 'route1',],
            ['gate' => 'route1',],
        ];

        $this->logger->expects($this->never())->method('error');

        $result = $this->runEvaluationProcess($config);
        $this->assertTrue($result);
    }
}
