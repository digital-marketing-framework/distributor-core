<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\Model\Configuration;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Tests\ListMapTestTrait;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfiguration;
use PHPUnit\Framework\TestCase;

class DistributorConfigurationTest extends TestCase
{
    use ListMapTestTrait;

    protected DistributorConfiguration $subject;

    /** @test */
    public function dataProviderFound(): void
    {
        $conf = [
            'conf1' => 'val1',
            'conf2' => 'val2',
        ];
        $configList = [
            [
                'dataProcessing' => [
                    'dataProviders' => [
                        'dataProvider1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new DistributorConfiguration($configList);
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
                'dataProcessing' => [
                    'dataProviders' => [
                        'dataProvider1' => $conf,
                    ],
                ],
            ],
        ];
        $this->subject = new DistributorConfiguration($configList);
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
                'integrations' => [
                    'integration1' => [
                        'outboundRoutes' => [
                            'routeId1' => $this->getRouteConfig($conf, 'route1', 'routeId1', 10),
                        ],
                    ],
                ],
            ],
        ];
        $this->subject = new DistributorConfiguration($configList);

        $result = $this->subject->getOutboundRouteConfiguration('integration1', 'routeId1');
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
                'integrations' => [
                    'integration1' => [
                        'outboundRoutes' => [
                            'routeId1' => $this->getRouteConfig($conf, 'route1', 'routeId1', 10),
                        ],
                    ],
                ],
            ],
        ];
        $this->subject = new DistributorConfiguration($configList);
        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->subject->getOutboundRouteConfiguration('integration1', 'routeId2');
    }

    /** @test */
    public function routeLabelSinglePass(): void
    {
        $configList = [
            [
                'integrations' => [
                    'integration1' => [
                        'outboundRoutes' => [
                            'routeId1' => $this->getRouteConfig([], 'route1', 'routeId1', 10),
                            'routeId2' => $this->getRouteConfig([], 'route2', 'routeId2', 20),
                        ],
                    ],
                ],
            ],
        ];
        $this->subject = new DistributorConfiguration($configList);
        $this->assertEquals('route1', $this->subject->getOutboundRouteLabel('integration1', 'routeId1'));
        $this->assertEquals('route2', $this->subject->getOutboundRouteLabel('integration1', 'routeId2'));
    }

    /** @test */
    public function routeLabelMultiplePassesWithoutPassNames(): void
    {
        $configList = [
            [
                'integrations' => [
                    'integration1' => [
                        'outboundRoutes' => [
                            'routeId1' => $this->getRouteConfig([], 'route1', 'routeId1', 10),
                            'routeId2' => $this->getRouteConfig([], 'route1', 'routeId2', 20),
                        ],
                    ],
                ],
            ],
        ];
        $this->subject = new DistributorConfiguration($configList);
        $this->assertEquals('route1#1', $this->subject->getOutboundRouteLabel('integration1', 'routeId1'));
        $this->assertEquals('route1#2', $this->subject->getOutboundRouteLabel('integration1', 'routeId2'));
    }

    /** @test */
    public function routeLabelSinglePassWithPassName(): void
    {
        $configList = [
            [
                'integrations' => [
                    'integration1' => [
                        'outboundRoutes' => [
                            'routeId1' => $this->getRouteConfig([], 'route1', 'routeId1', 10, 'passName1'),
                            'routeId2' => $this->getRouteConfig([], 'route2', 'routeId2', 20, 'passName2'),
                        ],
                    ],
                ],
            ],
        ];
        $this->subject = new DistributorConfiguration($configList);
        $this->assertEquals('route1', $this->subject->getOutboundRouteLabel('integration1', 'routeId1'));
        $this->assertEquals('route2', $this->subject->getOutboundRouteLabel('integration1', 'routeId2'));
    }

    /** @test */
    public function routeLabelMultiplePassesWithPassName(): void
    {
        $configList = [
            [
                'integrations' => [
                    'integration1' => [
                        'outboundRoutes' => [
                            'routeId1' => $this->getRouteConfig([], 'route1', 'routeId1', 10, 'passName1'),
                            'routeId2' => $this->getRouteConfig([], 'route1', 'routeId2', 20, 'passName2'),
                        ],
                    ],
                ],
            ],
        ];
        $this->subject = new DistributorConfiguration($configList);
        $this->assertEquals('route1#passName1', $this->subject->getOutboundRouteLabel('integration1', 'routeId1'));
        $this->assertEquals('route1#passName2', $this->subject->getOutboundRouteLabel('integration1', 'routeId2'));
    }
}
