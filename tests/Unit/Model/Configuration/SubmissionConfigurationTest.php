<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\Model\Configuration;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Tests\ListMapTestTrait;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfiguration;
use PHPUnit\Framework\TestCase;

class SubmissionConfigurationTest extends TestCase
{
    use ListMapTestTrait;

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

    /**
     * @param array<string,mixed> $conf
     *
     * @return array{uuid:string,weight:int,value:array<string,mixed>}
     */
    protected function getRouteConfig(array $conf, string $routeName, string $routeId, int $weight = 10, string $passName = ''): array
    {
        return $this->createListItem([
            'type' => $routeName,
            'pass' => $passName,
            'config' => [
                $routeName => $conf,
            ],
        ], $routeId, $weight);
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
                        'routeId1' => $this->getRouteConfig($conf, 'route1', 'routeId1', 10),
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);

        $result = $this->subject->getRouteConfiguration('routeId1');
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
                        'routeId1' => $this->getRouteConfig($conf, 'route1', 'routeId1', 10),
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->subject->getRouteConfiguration('routeId2');
    }

    /** @test */
    public function routeLabelSinglePass(): void
    {
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'routeId1' => $this->getRouteConfig([], 'route1', 'routeId1', 10),
                        'routeId2' => $this->getRouteConfig([], 'route2', 'routeId2', 20),
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $this->assertEquals('route1', $this->subject->getRouteLabel('routeId1'));
        $this->assertEquals('route2', $this->subject->getRouteLabel('routeId2'));
    }

    /** @test */
    public function routeLabelMultiplePassesWithoutPassNames(): void
    {
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'routeId1' => $this->getRouteConfig([], 'route1', 'routeId1', 10),
                        'routeId2' => $this->getRouteConfig([], 'route1', 'routeId2', 20),
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $this->assertEquals('route1#1', $this->subject->getRouteLabel('routeId1'));
        $this->assertEquals('route1#2', $this->subject->getRouteLabel('routeId2'));
    }

    /** @test */
    public function routeLabelSinglePassWithPassName(): void
    {
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'routeId1' => $this->getRouteConfig([], 'route1', 'routeId1', 10, 'passName1'),
                        'routeId2' => $this->getRouteConfig([], 'route2', 'routeId2', 20, 'passName2'),
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $this->assertEquals('route1', $this->subject->getRouteLabel('routeId1'));
        $this->assertEquals('route2', $this->subject->getRouteLabel('routeId2'));
    }

    /** @test */
    public function routeLabelMultiplePassesWithPassName(): void
    {
        $configList = [
            [
                'distributor' => [
                    'routes' => [
                        'routeId1' => $this->getRouteConfig([], 'route1', 'routeId1', 10, 'passName1'),
                        'routeId2' => $this->getRouteConfig([], 'route1', 'routeId2', 20, 'passName2'),
                    ],
                ],
            ],
        ];
        $this->subject = new SubmissionConfiguration($configList);
        $this->assertEquals('route1#passName1', $this->subject->getRouteLabel('routeId1'));
        $this->assertEquals('route1#passName2', $this->subject->getRouteLabel('routeId2'));
    }
}
