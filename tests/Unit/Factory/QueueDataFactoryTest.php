<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\Factory;

use DigitalMarketingFramework\Core\ConfigurationDocument\ConfigurationDocumentManagerInterface;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Model\Queue\Job;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Tests\ListMapTestTrait;
use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceManagerInterface;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSource\DistributorDataSourceInterface;
use DigitalMarketingFramework\Distributor\Core\Tests\Model\Data\Value\InvalidValue;
use DigitalMarketingFramework\Distributor\Core\Tests\Model\Data\Value\StringValue;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(QueueDataFactory::class)]
class QueueDataFactoryTest extends TestCase
{
    use ListMapTestTrait;

    protected QueueDataFactory $subject;

    protected DataInterface&MockObject $submissionData;

    protected DistributorConfigurationInterface&MockObject $submissionConfiguration;

    protected ContextInterface&MockObject $submissionContext;

    protected ConfigurationDocumentManagerInterface&MockObject $configurationDocumentManager;

    protected DistributorDataSourceManagerInterface&MockObject $distributorDataSourceManager;

    /** @var array<string,array<array<string,mixed>>> */
    protected array $configurationDocuments = [];

    /** @var array<string,string> */
    protected array $dataSources = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationDocumentManager = $this->createMock(ConfigurationDocumentManagerInterface::class);
        $this->configurationDocumentManager->method('getConfigurationStackFromConfiguration')->willReturnCallback(fn (array $configuration) => [$configuration]);
        $this->configurationDocumentManager->method('getConfigurationStackFromDocument')->willReturnCallback(fn (string $document) => $this->configurationDocuments[$document] ?? '');

        $this->distributorDataSourceManager = $this->createMock(DistributorDataSourceManagerInterface::class);
        $this->distributorDataSourceManager->method('getDataSourceById')->willReturnCallback(
            function (string $dataSourceId) {
                if (!isset($this->dataSources[$dataSourceId])) {
                    return null;
                }

                $dataSource = $this->createMock(DistributorDataSourceInterface::class);
                $dataSource->method('getConfigurationDocument')->willReturn($this->dataSources[$dataSourceId]);

                return $dataSource;
            }
        );

        $this->subject = new QueueDataFactory();
        $this->subject->setConfigurationDocumentManager($this->configurationDocumentManager);
        $this->subject->setDistributorDataSourceManager($this->distributorDataSourceManager);
    }

    /**
     * @param array<string,mixed> $configuration
     */
    protected function addDataSource(string $id, string $document, array $configuration): void
    {
        $this->dataSources[$id] = $document;
        $this->configurationDocuments[$document] = [$configuration];
    }

    /**
     * @param array<string,array<string,mixed>> $routeConfigs
     *
     * @return array<int,array<string,mixed>>
     */
    protected static function createRouteConfig(string $integrationName, string $routeName, array $routeConfigs): array
    {
        $config = [
            'integrations' => [
                $integrationName => [
                    'outboundRoutes' => [],
                ],
            ],
        ];
        $weight = 10;
        foreach ($routeConfigs as $routeId => $routeConfig) {
            $config['integrations'][$integrationName]['outboundRoutes'][$routeId] = static::createListItem([
                'type' => $routeName,
                'pass' => '',
                'config' => [
                    $routeName => $routeConfig,
                ],
            ], $routeId, $weight);
            $weight += 10;
        }

        return [$config];
    }

    #[Test]
    public function convertSubmissionWithStringValueToJob(): void
    {
        $data = [
            'field1' => 'value1',
        ];
        $configuration = $this->createRouteConfig('integration1', 'route1', ['routeId1' => [], 'routeId2' => []]);
        $submission = new SubmissionDataSet('datasource1', ['dsContextA' => 'A'], $data, $configuration, ['timestamp' => 1716482226]);
        $job = $this->subject->convertSubmissionToJob($submission, 'integration1', 'routeId2');
        $this->assertEquals([
            'integration' => 'integration1',
            'routeId' => 'routeId2',
            'submission' => [
                'data' => [
                    'field1' => ['type' => 'string', 'value' => 'value1'],
                ],
                'dataSourceId' => 'datasource1',
                'dataSourceContext' => ['dsContextA' => 'A'],
                'context' => ['timestamp' => 1716482226],
            ],
        ], $job->getData());
        $this->assertEquals('440BE46ADDF60B1D17EB6E44DC56C744', $job->getHash());
        $this->assertEquals('440BE#route1#2', $job->getLabel());
    }

    #[Test]
    public function convertSubmissionWithComplexFieldToJob(): void
    {
        $data = [
            'field1' => new StringValue('value1'),
        ];
        $configuration = $this->createRouteConfig('integration1', 'route1', ['routeId1' => [], 'routeId2' => []]);
        $submission = new SubmissionDataSet('datasource1', ['dsContextA' => 'A'], $data, $configuration, ['timestamp' => 1716482226]);
        $job = $this->subject->convertSubmissionToJob($submission, 'integration1', 'routeId2');
        $this->assertEquals([
            'integration' => 'integration1',
            'routeId' => 'routeId2',
            'submission' => [
                'data' => [
                    'field1' => ['type' => StringValue::class, 'value' => ['value' => 'value1']],
                ],
                'dataSourceId' => 'datasource1',
                'dataSourceContext' => ['dsContextA' => 'A'],
                'context' => ['timestamp' => 1716482226],
            ],
        ], $job->getData());
        $this->assertEquals('301342137702037436653AF526B32927', $job->getHash());
        $this->assertEquals('30134#route1#2', $job->getLabel());
    }

    #[Test]
    public function convertSubmissionWithInvalidValueToJob(): void
    {
        $data = [
            'field1' => new InvalidValue(),
        ];
        $configuration = $this->createRouteConfig('integration1', 'route1', ['routeId1' => [], 'routeId2' => []]);
        $submission = new SubmissionDataSet('datasource1', ['dsContextA' => 'A'], $data, $configuration, ['timestamp' => 1716482226]); // @phpstan-ignore-line this test case specifically checks how the system handles invalid data

        $this->expectException(InvalidArgumentException::class);
        $this->subject->convertSubmissionToJob($submission, 'integration1', 'routeId2');
    }

    #[Test]
    public function convertSubmissionWithDataConfigurationAndContextToJob(): void
    {
        $data = [
            'field1' => 'value1',
        ];
        $configuration = $this->createRouteConfig('integration1', 'route1', ['routeId1' => []]);
        $configuration[0] += [
            'globalConfKey1' => 'globalConfValue1',
            'globalConfKey2' => [
                'globalConfKey2.1' => 'globalConfValue2.1',
                'globalConfKey2.2' => 'globalConfValue2.2',
            ],
        ];
        $context = [
            'timestamp' => 1716482226,
            'contextKey1' => 'contextValue1',
            'contextKey2' => [
                'contextKey2.1' => 'contextValue2.1',
                'contextKey2.2' => 'contextValue2.2',
            ],
        ];
        $submission = new SubmissionDataSet('datasource1', ['dsContextA' => 'A'], $data, $configuration, $context);

        $job = $this->subject->convertSubmissionToJob($submission, 'integration1', 'routeId1');
        $this->assertEquals([
            'integration' => 'integration1',
            'routeId' => 'routeId1',
            'submission' => [
                'data' => [
                    'field1' => ['type' => 'string', 'value' => 'value1'],
                ],
                'dataSourceId' => 'datasource1',
                'dataSourceContext' => ['dsContextA' => 'A'],
                'context' => $context,
            ],
        ], $job->getData());
        $this->assertEquals('0B322C02720E7332637595661B980209', $job->getHash());
        $this->assertEquals('0B322#route1', $job->getLabel());
    }

    /**
     * @param array{
     *   data:array<string,array{type:string,value:mixed}>,
     *   dataSourceId:string,
     *   dataSourceContext:array<string,mixed>,
     *   context:array<string,mixed>
     * } $submissionData
     */
    protected static function createJob(array $submissionData, string $integration, string $routeId, string $hash = ''): JobInterface
    {
        $job = new Job();
        $job->setData([
            'integration' => $integration,
            'routeId' => $routeId,
            'submission' => $submissionData,
        ]);
        $job->setHash($hash);

        return $job;
    }

    #[Test]
    public function convertJobWithStringValueToSubmission(): void
    {
        $this->addDataSource('datasource1', 'configurationDocument1', []);
        $job = $this->createJob([
            'data' => [
                'field1' => ['type' => 'string', 'value' => 'value1'],
            ],
            'dataSourceId' => 'datasource1',
            'dataSourceContext' => ['dsContextA' => 'A'],
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $submission = $this->subject->convertJobToSubmission($job);
        $this->assertTrue($submission->getData()->fieldExists('field1'));
        $this->assertEquals('value1', $submission->getData()['field1']);
    }

    #[Test]
    public function convertJobWithComplexFieldToSubmission(): void
    {
        $this->addDataSource('datasource1', 'configurationDocument1', []);
        $job = $this->createJob([
            'data' => [
                'field1' => ['type' => StringValue::class, 'value' => ['value' => 'value1']],
            ],
            'dataSourceId' => 'datasource1',
            'dataSourceContext' => ['dsContextA' => 'A'],
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $submission = $this->subject->convertJobToSubmission($job);
        $this->assertTrue($submission->getData()->fieldExists('field1'));
        $this->assertInstanceOf(StringValue::class, $submission->getData()['field1']);
        $this->assertEquals('value1', (string)$submission->getData()['field1']);
        $this->assertEquals(['value' => 'value1'], $submission->getData()['field1']->pack());
    }

    #[Test]
    public function convertJobWithInvalidValueToSubmission(): void
    {
        $this->addDataSource('datasource1', 'configurationDocument1', []);
        $job = $this->createJob([
            'data' => [
                'field1' => ['type' => InvalidValue::class, 'value' => ['value' => 'value1']],
            ],
            'dataSourceId' => 'datasource1',
            'dataSourceContext' => ['dsContextA' => 'A'],
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->subject->convertJobToSubmission($job);
    }

    #[Test]
    public function convertJobWithUnknownFieldToSubmission(): void
    {
        $this->addDataSource('datasource1', 'configurationDocument1', []);
        $job = $this->createJob([
            'data' => [
                'field1' => ['type' => 'DigitalMarketingFramework\Distributor\Core\Model\Data\Value\ValueClassThatDoesNotExist', 'value' => ['value1']],
            ],
            'dataSourceId' => 'datasource1',
            'dataSourceContext' => ['dsContextA' => 'A'],
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->subject->convertJobToSubmission($job);
    }

    /**
     * @return array<array{SubmissionDataSetInterface,JobInterface,string,string}>
     */
    public static function hashDataProvider(): array
    {
        $config = static::createRouteConfig('integration1', 'route1', ['routeId1' => []]);

        return [
            [
                new SubmissionDataSet(
                    'datasource1',
                    ['dsContextA' => 'A'],
                    ['field1' => 'value1'],
                    $config,
                    ['timestamp' => 1716482226, 'context1' => 'contextValue1']
                ),
                static::createJob(
                    [
                        'data' => ['field1' => ['type' => 'string', 'value' => 'value1']],
                        'dataSourceId' => 'datasource1',
                        'dataSourceContext' => ['dsContextA' => 'A'],
                        'context' => ['timestamp' => 1716482226, 'context1' => 'contextValue1'],
                    ],
                    'integration1', 'routeId1'
                ),
                '0F9FD178F360D35BDB6662752B110075',
                'configurationDocument1Content',
            ],
        ];
    }

    #[Test]
    #[DataProvider('hashDataProvider')]
    public function getSubmissionHash(SubmissionDataSetInterface $submission, JobInterface $job, string $expectedHash): void
    {
        $hash = $this->subject->getSubmissionHash($submission);
        $this->assertEquals($expectedHash, $hash);
    }

    #[Test]
    #[DataProvider('hashDataProvider')]
    public function getJobHash(SubmissionDataSetInterface $submission, JobInterface $job, string $expectedHash): void
    {
        $hash = $this->subject->getJobHash($job);
        $this->assertEquals($expectedHash, $hash);
    }

    #[Test]
    #[DataProvider('hashDataProvider')]
    public function getSubmissionAndConvertedJobHash(SubmissionDataSetInterface $submission, JobInterface $job, string $expectedHash, string $configurationDocument): void
    {
        $this->addDataSource($submission->getDataSourceId(), $configurationDocument, $submission->getConfiguration()->getRootConfiguration());

        $submissionHash = $this->subject->getSubmissionHash($submission);
        $convertedJob = $this->subject->convertSubmissionToJob($submission, 'integration1', 'routeId1');
        $convertedJobHash = $this->subject->getJobHash($convertedJob);
        $convertedSubmission = $this->subject->convertJobToSubmission($convertedJob);
        $convertedSubmissionHash = $this->subject->getSubmissionHash($convertedSubmission);

        $this->assertEquals($submissionHash, $convertedJobHash);
        $this->assertEquals($convertedJobHash, $convertedSubmissionHash);
    }

    /**
     * @throws DigitalMarketingFrameworkException
     */
    #[Test]
    #[DataProvider('hashDataProvider')]
    public function getJobAndConvertedSubmissionHash(SubmissionDataSetInterface $submission, JobInterface $job, string $expectedHash, string $configurationDocument): void
    {
        $this->addDataSource($submission->getDataSourceId(), $configurationDocument, $submission->getConfiguration()->getRootConfiguration());

        $jobHash = $this->subject->getJobHash($job);
        $convertedSubmission = $this->subject->convertJobToSubmission($job);
        $convertedSubmissionHash = $this->subject->getSubmissionHash($convertedSubmission);
        $convertedJob = $this->subject->convertSubmissionToJob($convertedSubmission, 'integration1', 'routeId1');
        $convertedJobHash = $this->subject->getJobHash($convertedJob);

        $this->assertEquals($jobHash, $convertedSubmissionHash);
        $this->assertEquals($convertedSubmissionHash, $convertedJobHash);
    }

    #[Test]
    public function getSubmissionLabel(): void
    {
        $submission = new SubmissionDataSet(
            'datasource1',
            ['dsContextA' => 'A'],
            [],
            [
                [
                    'integrations' => [
                        'integration1' => [
                            'outboundRoutes' => [
                                'routeId1' => $this->createListItem([
                                    'type' => 'route1',
                                    'config' => [
                                        'route1' => [],
                                    ],
                                ], 'routeId1', 10),
                            ],
                        ],
                    ],
                ],
            ],
            ['timestamp' => 1716482226]
        );
        $label = $this->subject->getSubmissionLabel($submission, 'integration1', 'routeId1');
        $this->assertEquals('C306F#route1', $label);
    }

    #[Test]
    public function getJobLabel(): void
    {
        $config = [
            'integrations' => [
                'integration1' => [
                    'outboundRoutes' => [
                        'routeId1' => $this->createListItem([
                            'type' => 'route1',
                            'config' => [
                                'route1' => [],
                            ],
                        ], 'routeId1', 10),
                    ],
                ],
            ],
        ];
        $this->addDataSource('datasource1', 'condigurationDocument1', $config);
        $job = $this->createJob([
            'data' => [],
            'dataSourceId' => 'datasource1',
            'dataSourceContext' => ['dsContextA' => 'A'],
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1', 'ABCDEFGHIJKLMNO');
        $label = $this->subject->getJobLabel($job);
        $this->assertEquals('ABCDE#route1', $label);
    }

    #[Test]
    public function getJobLabelWithoutOwnHash(): void
    {
        $config = [
            'integrations' => [
                'integration1' => [
                    'outboundRoutes' => [
                        'routeId1' => $this->createListItem([
                            'type' => 'route1',
                            'config' => [
                                'route1' => [],
                            ],
                        ], 'routeId1', 10),
                    ],
                ],
            ],
        ];
        $this->addDataSource('datasource1', 'configurationDocument1', $config);
        $job = $this->createJob([
            'data' => [],
            'dataSourceId' => 'datasource1',
            'dataSourceContext' => ['dsContextA' => 'A'],
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $label = $this->subject->getJobLabel($job);
        $this->assertEquals('C306F#route1', $label);
    }

    #[Test]
    public function getJobRouteId(): void
    {
        $config = [
            'integrations' => [
                'integration1' => [
                    'outboundRoutes' => [
                        'routeId1' => $this->createListItem([
                            'type' => 'route1',
                            'config' => [
                                'route1' => [],
                            ],
                        ], 'routeId1', 10),
                    ],
                ],
            ],
        ];
        $this->addDataSource('datasource1', 'configurationDocument1', $config);
        $job = $this->createJob([
            'data' => [],
            'dataSourceId' => 'datasource1',
            'dataSourceContext' => ['dsContextA' => 'A'],
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $route = $this->subject->getJobRouteId($job);
        $this->assertEquals('routeId1', $route);
    }

    #[Test]
    public function getJobIntegrationName(): void
    {
        $config = [
            'integrations' => [
                'integration1' => [
                    'outboundRoutes' => [
                        'routeId1' => $this->createListItem([
                            'type' => 'route1',
                            'config' => [
                                'route1' => [],
                            ],
                        ], 'routeId1', 10),
                    ],
                ],
            ],
        ];
        $this->addDataSource('datasource1', 'configurationDocument1', $config);
        $job = $this->createJob([
            'data' => [],
            'dataSourceId' => 'datasource1',
            'dataSourceContext' => ['dsContextA' => 'A'],
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $integration = $this->subject->getJobRouteIntegrationName($job);
        $this->assertEquals('integration1', $integration);
    }
}
