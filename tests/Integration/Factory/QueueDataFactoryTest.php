<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration\Factory;

use DigitalMarketingFramework\Core\ConfigurationDocument\ConfigurationDocumentManagerInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\Value\FileValue;
use DigitalMarketingFramework\Core\Model\Data\Value\MultiValue;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Core\Model\Queue\Job;
use DigitalMarketingFramework\Core\Tests\ListMapTestTrait;
use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceManagerInterface;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributor\Core\Model\Data\Value\DiscreteMultiValue;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSource\DistributorDataSourceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(QueueDataFactory::class)]
class QueueDataFactoryTest extends TestCase
{
    use ListMapTestTrait;

    protected ConfigurationDocumentManagerInterface&MockObject $configurationDocumentManager;

    protected DistributorDataSourceManagerInterface&MockObject $distributorDataSourceManager;

    protected QueueDataFactory $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurationDocumentManager = $this->createMock(ConfigurationDocumentManagerInterface::class);
        $this->configurationDocumentManager->method('getConfigurationStackFromConfiguration')->willReturnCallback(static fn (array $configuration) => [$configuration]);

        $this->distributorDataSourceManager = $this->createMock(DistributorDataSourceManagerInterface::class);

        $this->subject = new QueueDataFactory();
        $this->subject->setConfigurationDocumentManager($this->configurationDocumentManager);
        $this->subject->setDistributorDataSourceManager($this->distributorDataSourceManager);
    }

    /**
     * @return array<array{id:string,integration:string}>
     */
    protected static function routeIdProvider(): array
    {
        return [
            [
                'id' => 'routeId1',
                'integration' => 'integration1',
            ],
            [
                'id' => 'routeId2',
                'integration' => 'integration2',
            ],
        ];
    }

    /**
     * @return array<array{0:array<string,string|ValueInterface>,1:array<string,array{type:string,value:mixed}>}>
     */
    protected static function packDataProvider(): array
    {
        return [
            [[], []],
            [
                [
                    'field1' => 'value1',
                    'field2' => 'value2',
                    'field3' => new MultiValue(),
                    'field4' => new MultiValue(['5', '7', '17']),
                    'field5' => new DiscreteMultiValue(),
                    'field6' => new DiscreteMultiValue(['5', '7', '17']),
                    'field7' => new FileValue('path1', 'name1', 'url1', 'type1'),
                ],
                [
                    'field1' => ['type' => 'string', 'value' => 'value1'],
                    'field2' => ['type' => 'string', 'value' => 'value2'],
                    'field3' => ['type' => MultiValue::class, 'value' => []],
                    'field4' => ['type' => MultiValue::class, 'value' => [['type' => 'string', 'value' => '5'], ['type' => 'string', 'value' => '7'], ['type' => 'string', 'value' => '17']]],
                    'field5' => ['type' => DiscreteMultiValue::class, 'value' => []],
                    'field6' => ['type' => DiscreteMultiValue::class, 'value' => [['type' => 'string', 'value' => '5'], ['type' => 'string', 'value' => '7'], ['type' => 'string', 'value' => '17']]],
                    'field7' => ['type' => FileValue::class, 'value' => ['fileName' => 'name1', 'publicUrl' => 'url1', 'relativePath' => 'path1', 'mimeType' => 'type1']],
                ],
            ],
        ];
    }

    /**
     * @return array<array{0:array<string,mixed>,1:array<string,mixed>}>
     */
    protected static function packConfigurationProvider(): array
    {
        return [
            [
                [ // config
                    'integrations' => [
                        'integration1' => [
                            'outboundRoutes' => [
                                'routeId1' => static::createListItem([
                                    'type' => 'route1',
                                    'pass' => '',
                                    'config' => [
                                        'route1' => [
                                            'confKey1' => 'confValue1',
                                        ],
                                    ],
                                ], 'routeId1', 10),
                            ],
                        ],
                        'integration2' => [
                            'outboundRoutes' => [
                                'routeId2' => static::createListItem([
                                    'type' => 'route1',
                                    'pass' => '',
                                    'config' => [
                                        'route1' => [
                                            'confKey2' => 'confValue2',
                                        ],
                                    ],
                                ], 'routeId2', 20),
                            ],
                        ],
                    ],
                ],
                [ // packed config
                    'integrations' => [
                        'integration1' => [
                            'outboundRoutes' => [
                                'routeId1' => static::createListItem([
                                    'type' => 'route1',
                                    'pass' => '',
                                    'config' => [
                                        'route1' => [
                                            'confKey1' => 'confValue1',
                                        ],
                                    ],
                                ], 'routeId1', 10),
                            ],
                        ],
                        'integration2' => [
                            'outboundRoutes' => [
                                'routeId2' => static::createListItem([
                                    'type' => 'route1',
                                    'pass' => '',
                                    'config' => [
                                        'route1' => [
                                            'confKey2' => 'confValue2',
                                        ],
                                    ],
                                ], 'routeId2', 20),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<array{array<string,mixed>,array<string,mixed>}>
     */
    protected static function packContextProvider(): array
    {
        return [
            [['timestamp' => 1716482226], ['timestamp' => 1716482226]],
            [['timestamp' => 1716482226, 'contextKey1' => 'contextValue1'], ['timestamp' => 1716482226, 'contextKey1' => 'contextValue1']],
        ];
    }

    /**
     * @return array<array{
     *   string,
     *   array<string,mixed>,
     *   string,
     *   array<string,string|ValueInterface>,
     *   array<int,array<string,mixed>>,
     *   array<string,mixed>,
     *   string,
     *   string,
     *   array{
     *     integration:string,
     *     routeId:string,
     *     submission:array{
     *       data:array<string,array{type:string,value:mixed}>,
     *       dataSourceId:string,
     *       dataSourceContext:array<string,mixed>,
     *       context:array<string,mixed>
     *     }
     *   }
     * }>
     */
    public static function packProvider(): array
    {
        $result = [];
        foreach (static::packDataProvider() as [$data, $packedData]) {
            foreach (static::packConfigurationProvider() as [$configuration, $packedConfiguration]) {
                foreach (static::packContextProvider() as [$context, $packedContext]) {
                    foreach (static::routeIdProvider() as $routeId) {
                        $result[] = [
                            'datasource1',
                            ['dsContextA' => 'A'],
                            'configurationDocument1',
                            $data,
                            [$configuration],
                            $context,
                            $routeId['id'],
                            $routeId['integration'],
                            [
                                'routeId' => $routeId['id'],
                                'integration' => $routeId['integration'],
                                'submission' => [
                                    'data' => $packedData,
                                    'dataSourceId' => 'datasource1',
                                    'dataSourceContext' => ['dsContextA' => 'A'],
                                    'context' => $packedContext,
                                ],
                            ],
                        ];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array<string,mixed> $dataSourceContext
     * @param array<string,string|ValueInterface> $data
     * @param array<int,array<string,mixed>> $configuration
     * @param array<string,mixed> $context
     * @param array{
     *     routeId:string,
     *     integration:string,
     *     submission:array{
     *       data:array<string,array{type:string,value:mixed}>,
     *       dataSourceId: string,
     *       dataSourceContext: array<string,mixed>,
     *       context:array<string,mixed>
     *     }
     *   } $jobData
     */
    #[Test]
    #[DataProvider('packProvider')]
    public function pack(string $dataSourceId, array $dataSourceContext, string $configurationDocument, array $data, array $configuration, array $context, string $routeId, string $integration, array $jobData): void
    {
        $submission = new SubmissionDataSet($dataSourceId, $dataSourceContext, $data, $configuration, $context);
        $job = $this->subject->convertSubmissionToJob($submission, $integration, $routeId);
        $this->assertEquals($jobData, $job->getData());
    }

    /**
     * @param array<string,mixed> $dataSourceContext
     * @param array<string,string|ValueInterface> $data
     * @param array<int,array<string,mixed>> $configuration
     * @param array<string,mixed> $context
     * @param array{
     *     routeId:string,
     *     integration:string,
     *     submission:array{
     *       data:array<string,array{type:string,value:mixed}>,
     *       dataSourceId: string,
     *       dataSourceContext: array<string,mixed>,
     *       context:array<string,mixed>
     *     }
     *   } $jobData
     *
     * @throws DigitalMarketingFrameworkException
     */
    #[Test]
    #[DataProvider('packProvider')]
    public function unpack(string $dataSourceId, array $dataSourceContext, string $configurationDocument, array $data, array $configuration, array $context, string $routeId, string $integration, array $jobData): void
    {
        $job = new Job();
        $job->setData($jobData);

        $dataSource = $this->createMock(DistributorDataSourceInterface::class);
        $dataSource->method('getConfigurationDocument')->willReturn($configurationDocument);

        $this->distributorDataSourceManager->method('getDataSourceById')
            ->with($dataSourceId, $dataSourceContext)
            ->willReturn($dataSource);

        $this->configurationDocumentManager->method('getConfigurationStackFromDocument')
            ->with($configurationDocument)
            ->willReturn($configuration);

        $submission = $this->subject->convertJobToSubmission($job);

        $this->assertEquals($data, $submission->getData()->toArray());
        $this->assertEquals($configuration, $submission->getConfiguration()->toArray());
        $this->assertEquals($context, $submission->getContext()->toArray());
    }

    /**
     * @param array<string,mixed> $dataSourceContext
     * @param array<string,string|ValueInterface> $data
     * @param array<int,array<string,mixed>> $configuration
     * @param array<string,mixed> $context
     * @param array{
     *     routeId:string,
     *     integration:string,
     *     submission:array{
     *       data:array<string,array{type:string,value:mixed}>,
     *       dataSourceId: string,
     *       dataSourceContext: array<string,mixed>,
     *       context:array<string,mixed>
     *     }
     *   } $jobData
     *
     * @throws DigitalMarketingFrameworkException
     */
    #[Test]
    #[DataProvider('packProvider')]
    public function packUnpack(string $dataSourceId, array $dataSourceContext, string $configurationDocument, array $data, array $configuration, array $context, string $routeId, string $integration, array $jobData): void
    {
        $dataSource = $this->createMock(DistributorDataSourceInterface::class);
        $dataSource->method('getConfigurationDocument')->willReturn($configurationDocument);

        $this->distributorDataSourceManager->method('getDataSourceById')
            ->with($dataSourceId, $dataSourceContext)
            ->willReturn($dataSource);

        $this->configurationDocumentManager->method('getConfigurationStackFromDocument')
            ->with($configurationDocument)
            ->willReturn($configuration);

        $submission = new SubmissionDataSet($dataSourceId, $dataSourceContext, $data, $configuration, $context);
        $job = $this->subject->convertSubmissionToJob($submission, $integration, $routeId);
        $this->assertEquals($jobData, $job->getData());

        /** @var SubmissionDataSetInterface */
        $result = $this->subject->convertJobToSubmission($job);
        $this->assertEquals($data, $result->getData()->toArray());
        $this->assertEquals($configuration, $result->getConfiguration()->toArray());
        $this->assertEquals($context, $result->getContext()->toArray());
    }
}
