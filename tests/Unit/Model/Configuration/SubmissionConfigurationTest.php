<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\Model\Configuration;

use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfiguration;
use PHPUnit\Framework\TestCase;

class SubmissionConfigurationTest extends TestCase
{
    protected SubmissionConfiguration $subject;

    /** @test */
    public function dataProviderFound(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
        ];
        $configList = [
            [
                'distributor' => [
                    'dataProviders' => [
                        'dataProvider1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $result = $this->subject->getDataProviderConfiguration('dataProvider1');
        $this->assertEquals($conf, $result);
    }

    /** @test */
    public function dataProviderNotFound(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
        ];
        $configList = [
            [
                'distributor' => [
                    'dataProviders' => [
                        'dataProvider1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $result = $this->subject->getDataProviderConfiguration('dataProvider2');
        $this->assertEquals([], $result);
    }

    /** @test */
    public function routeFound(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
        ];
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);

        $result = $this->subject->getRoutePassConfiguration('route1', 0);
        $this->assertEquals($conf, $result);
    }

    /** @test */
    public function routeNotFound(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
        ];
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $result = $this->subject->getRoutePassConfiguration('route2', 0);
        $this->assertEquals([], $result);
    }

    /** @test */
    public function routePassCountRouteWithoutPasses(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
        ];
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $result = $this->subject->getRoutePassCount('route1');
        $this->assertEquals(1, $result);
    }

    /** @test */
    public function routePassCountRouteNotFound(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
        ];
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $result = $this->subject->getRoutePassCount('route2');
        $this->assertEquals(1, $result);
    }

    /** @test */
    public function routePassConfiguration(): void
    {
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => [
                            'key1' => 'value1',
                            'key2' => 'value2',
                            'key3' => 'value3',
                            'passes' => [
                                [
                                    'key2' => 'value2b',
                                    'key4' => 'value4b'
                                ],
                                [
                                    'key3' => 'value3c',
                                    'key4' => 'value4c',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);

        $this->assertEquals(2, $this->subject->getRoutePassCount('route1'));

        $pass1 = $this->subject->getRoutePassConfiguration('route1', 0);
        $this->assertEquals([
            'key1' => 'value1',
            'key2' => 'value2b',
            'key3' => 'value3',
            'key4' => 'value4b',
        ], $pass1);

        $pass2 = $this->subject->getRoutePassConfiguration('route1', 1);
        $this->assertEquals([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3c',
            'key4' => 'value4c',
        ], $pass2);
    }

    /** @test */
    public function routePassConfigurationOverride(): void
    {
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => [
                            'key1' => 'value1',
                            'key2' => 'value2',
                            'key3' => 'value3',
                            'passes' => [
                                [
                                    'key2' => 'value2b',
                                    'key4' => 'value4b'
                                ],
                                [
                                    'key3' => 'value3c',
                                    'key4' => 'value4c',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'distributor' => [
                    'routes' => [
                        'route1' => [
                            'key1' => 'value1.2',
                            'passes' => [
                                0 => [
                                    'key2' => null,
                                    'key3' => 'value3b.2',
                                ],
                                2 => [
                                    'key1' => 'value1d.2',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);

        $this->assertEquals(3, $this->subject->getRoutePassCount('route1'));

        $pass1 = $this->subject->getRoutePassConfiguration('route1', 0);
        $this->assertEquals([
            'key1' => 'value1.2',
            'key3' => 'value3b.2',
            'key4' => 'value4b',
        ], $pass1);

        $pass2 = $this->subject->getRoutePassConfiguration('route1', 1);
        $this->assertEquals([
            'key1' => 'value1.2',
            'key2' => 'value2',
            'key3' => 'value3c',
            'key4' => 'value4c',
        ], $pass2);

        $pass3 = $this->subject->getRoutePassConfiguration('route1', 2);
        $this->assertEquals([
            'key1' => 'value1d.2',
            'key2' => 'value2',
            'key3' => 'value3',
        ], $pass3);
    }

    /** @test */
    public function routePassConfigurationIndicesNotContinuous(): void
    {
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => [
                            'key1' => 'value1',
                            'passes' => [
                                10 => [
                                    'key1' => 'value1.1',
                                ],
                                20 => [
                                    'key1' => 'value1.2',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $this->assertEquals(2, $this->subject->getRoutePassCount('route1'));

        $pass1 = $this->subject->getRoutePassConfiguration('route1', 0);
        $this->assertEquals('value1.1', $pass1['key1']);

        $pass2 = $this->subject->getRoutePassConfiguration('route1', 1);
        $this->assertEquals('value1.2', $pass2['key1']);
    }

    /** @test */
    public function routePassConfigurationIndicesNotNumerical(): void
    {
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => [
                            'key1' => 'value1',
                            'passes' => [
                                'pass1' => [
                                    'key1' => 'value1.1',
                                ],
                                'pass2' => [
                                    'key1' => 'value1.2',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $this->assertEquals(2, $this->subject->getRoutePassCount('route1'));

        $pass1 = $this->subject->getRoutePassConfiguration('route1', 0);
        $this->assertEquals('value1.1', $pass1['key1']);

        $pass2 = $this->subject->getRoutePassConfiguration('route1', 1);
        $this->assertEquals('value1.2', $pass2['key1']);
    }

    /** @test */
    public function routePassConfigurationOrder(): void
    {
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => [
                            'key1' => 'value1',
                            'passes' => [
                                20 => [
                                    'key1' => 'value1.2',
                                ],
                                10 => [
                                    'key1' => 'value1.1',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $this->assertEquals(2, $this->subject->getRoutePassCount('route1'));

        $pass1 = $this->subject->getRoutePassConfiguration('route1', 0);
        $this->assertEquals('value1.1', $pass1['key1']);

        $pass2 = $this->subject->getRoutePassConfiguration('route1', 1);
        $this->assertEquals('value1.2', $pass2['key1']);
    }

    /** @test */
    public function getRoutePassLabelOnNonExistingRouteBehavesLikeEmptyRoute(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
        ];
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $result = $this->subject->getRoutePassLabel('route2', 0);
        $this->assertEquals('route2', $result);
    }

    /** @test */
    public function getRoutePassLabelOnExistingRouteWithoutPasses(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
        ];
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $result = $this->subject->getRoutePassLabel('route1', 0);
        $this->assertEquals('route1', $result);
    }

    /** @test */
    public function getRoutePassLabelOnExistingRouteWithOnePass(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
            'passes' => [
                0 => [
                    'conf1' => 'val1.1',
                ],
            ],
        ];
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $result = $this->subject->getRoutePassLabel('route1', 0);
        $this->assertEquals('route1', $result);
    }

    /** @test */
    public function getRoutePassLabelOnExistingRouteWithOnePassOfWhichTheKeyIsNotNumeric(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
            'passes' => [
                'pass1' => [
                    'conf1' => 'val1.1',
                ],
            ],
        ];
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $result = $this->subject->getRoutePassLabel('route1', 0);
        $this->assertEquals('route1#pass1', $result);
    }

    /** @test */
    public function getRoutePassLabelOnExistingRouteWithOnePassOfWhichTheKeyIsNumericAndBiggerThanZero(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
            'passes' => [
                10 => [
                    'conf1' => 'val1.1',
                ],
            ],
        ];
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $result = $this->subject->getRoutePassLabel('route1', 0);
        $this->assertEquals('route1', $result);
    }

    /** @test */
    public function getRoutePassLabelOnExistingRouteWithMultiplePassesWithNumericKeys(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
            'passes' => [
                0 => [
                    'conf1' => 'val1.1',
                ],
                1 => [
                    'conf2' => 'val2.2',
                ],
            ],
        ];
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $this->assertEquals('route1#1', $this->subject->getRoutePassLabel('route1', 0));
        $this->assertEquals('route1#2', $this->subject->getRoutePassLabel('route1', 1));
    }

    /** @test */
    public function getRoutePassLabelOnExistingRouteWithMultiplePassesWithNonNumericKeys(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
            'passes' => [
                'pass1' => [
                    'conf1' => 'val1.1',
                ],
                'pass2' => [
                    'conf2' => 'val2.2',
                ],
            ],
        ];
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $this->assertEquals('route1#pass1', $this->subject->getRoutePassLabel('route1', 0));
        $this->assertEquals('route1#pass2', $this->subject->getRoutePassLabel('route1', 1));
    }

    /** @test */
    public function getRoutePassLabelOnExistingRouteWithMultiplePassesWithMixedKeys(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
            'passes' => [
                'pass1' => [
                    'conf1' => 'val1.1',
                ],
                10 => [
                    'conf1' => 'val1.2',
                ],
                'pass2' => [
                    'conf1' => 'val1.3',
                ],
            ],
        ];
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'route1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $this->assertEquals('route1#pass1', $this->subject->getRoutePassLabel('route1', 0));
        $this->assertEquals('route1#pass2', $this->subject->getRoutePassLabel('route1', 1));
        $this->assertEquals('route1#3', $this->subject->getRoutePassLabel('route1', 2));
    }
}
