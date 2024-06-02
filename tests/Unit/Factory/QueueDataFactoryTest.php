<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\Factory;

use DigitalMarketingFramework\Core\ConfigurationDocument\ConfigurationDocumentManagerInterface;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Model\Queue\Job;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Tests\ListMapTestTrait;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Tests\Model\Data\Value\InvalidValue;
use DigitalMarketingFramework\Distributor\Core\Tests\Model\Data\Value\StringValue;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory
 */
class QueueDataFactoryTest extends TestCase
{
    use ListMapTestTrait;

    protected QueueDataFactory $subject;

    protected DataInterface&MockObject $submissionData;

    protected DistributorConfigurationInterface&MockObject $submissionConfiguration;

    protected ContextInterface&MockObject $submissionContext;

    protected ConfigurationDocumentManagerInterface&MockObject $configurationDocumentManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationDocumentManager = $this->createMock(ConfigurationDocumentManagerInterface::class);
        $this->configurationDocumentManager->method('getConfigurationStackFromConfiguration')->willReturnCallback(static function ($configuration) {
            return [$configuration];
        });

        $this->subject = new QueueDataFactory($this->configurationDocumentManager);
    }

    /**
     * @param array<string,array<string,mixed>> $routeConfigs
     *
     * @return array<int,array<string,mixed>>
     */
    protected function createRouteConfig(string $integrationName, string $routeName, array $routeConfigs): array
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
            $config['integrations'][$integrationName]['outboundRoutes'][$routeId] = $this->createListItem([
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

    /** @test */
    public function convertSubmissionWithStringValueToJob(): void
    {
        $data = [
            'field1' => 'value1',
        ];
        $configuration = $this->createRouteConfig('integration1', 'route1', ['routeId1' => [], 'routeId2' => []]);
        $submission = new SubmissionDataSet($data, $configuration, ['timestamp' => 1716482226]);
        $job = $this->subject->convertSubmissionToJob($submission, 'integration1', 'routeId2');
        $this->assertEquals([
            'integration' => 'integration1',
            'routeId' => 'routeId2',
            'submission' => [
                'data' => [
                    'field1' => ['type' => 'string', 'value' => 'value1'],
                ],
                'configuration' => $configuration[0],
                'context' => ['timestamp' => 1716482226],
            ],
        ], $job->getData());
        $this->assertEquals('626B9273C7B1D464D4BDD3490ABBE844', $job->getHash());
        $this->assertEquals('626B9#route1#2', $job->getLabel());
    }

    /** @test */
    public function convertSubmissionWithComplexFieldToJob(): void
    {
        $data = [
            'field1' => new StringValue('value1'),
        ];
        $configuration = $this->createRouteConfig('integration1', 'route1', ['routeId1' => [], 'routeId2' => []]);
        $submission = new SubmissionDataSet($data, $configuration, ['timestamp' => 1716482226]);
        $job = $this->subject->convertSubmissionToJob($submission, 'integration1', 'routeId2');
        $this->assertEquals([
            'integration' => 'integration1',
            'routeId' => 'routeId2',
            'submission' => [
                'data' => [
                    'field1' => ['type' => StringValue::class, 'value' => ['value' => 'value1']],
                ],
                'configuration' => $configuration[0],
                'context' => ['timestamp' => 1716482226],
            ],
        ], $job->getData());
        $this->assertEquals('168762E27FFFF25C881CF8A4C7E05BD0', $job->getHash());
        $this->assertEquals('16876#route1#2', $job->getLabel());
    }

    /** @test */
    public function convertSubmissionWithInvalidValueToJob(): void
    {
        $data = [
            'field1' => new InvalidValue(),
        ];
        $configuration = $this->createRouteConfig('integration1', 'route1', ['routeId1' => [], 'routeId2' => []]);
        $submission = new SubmissionDataSet($data, $configuration, ['timestamp' => 1716482226]); // @phpstan-ignore-line this test case specifically checks how the system handles invalid data

        $this->expectException(InvalidArgumentException::class);
        $this->subject->convertSubmissionToJob($submission, 'integration1', 'routeId2');
    }

    /** @test */
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
        $submission = new SubmissionDataSet($data, $configuration, $context);

        $job = $this->subject->convertSubmissionToJob($submission, 'integration1', 'routeId1');
        $this->assertEquals([
            'integration' => 'integration1',
            'routeId' => 'routeId1',
            'submission' => [
                'data' => [
                    'field1' => ['type' => 'string', 'value' => 'value1'],
                ],
                'configuration' => $configuration[0],
                'context' => $context,
            ],
        ], $job->getData());
        $this->assertEquals('E36A036637418D5600C0AE4F5E65E79D', $job->getHash());
        $this->assertEquals('E36A0#route1', $job->getLabel());
    }

    /**
     * @param array{
     *   data:array<string,array{type:string,value:mixed}>,
     *   configuration:array<string,mixed>,
     *   context:array<string,mixed>
     * } $submissionData
     */
    protected function createJob(array $submissionData, string $integration, string $routeId, string $hash = ''): JobInterface
    {
        return new Job(
            data: [
                'integration' => $integration,
                'routeId' => $routeId,
                'submission' => $submissionData,
            ],
            hash: $hash
        );
    }

    /** @test */
    public function convertJobWithStringValueToSubmission(): void
    {
        $job = $this->createJob([
            'data' => [
                'field1' => ['type' => 'string', 'value' => 'value1'],
            ],
            'configuration' => [],
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $submission = $this->subject->convertJobToSubmission($job);
        $this->assertTrue($submission->getData()->fieldExists('field1'));
        $this->assertEquals('value1', $submission->getData()['field1']);
    }

    /** @test */
    public function convertJobWithComplexFieldToSubmission(): void
    {
        $job = $this->createJob([
            'data' => [
                'field1' => ['type' => StringValue::class, 'value' => ['value' => 'value1']],
            ],
            'configuration' => [],
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $submission = $this->subject->convertJobToSubmission($job);
        $this->assertTrue($submission->getData()->fieldExists('field1'));
        $this->assertInstanceOf(StringValue::class, $submission->getData()['field1']);
        $this->assertEquals('value1', (string)$submission->getData()['field1']);
        $this->assertEquals(['value' => 'value1'], $submission->getData()['field1']->pack());
    }

    /** @test */
    public function convertJobWithInvalidValueToSubmission(): void
    {
        $job = $this->createJob([
            'data' => [
                'field1' => ['type' => InvalidValue::class, 'value' => ['value' => 'value1']],
            ],
            'configuration' => [],
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->subject->convertJobToSubmission($job);
    }

    /** @test */
    public function convertJobWithUnknownFieldToSubmission(): void
    {
        $job = $this->createJob([
            'data' => [
                'field1' => ['type' => 'DigitalMarketingFramework\Distributor\Core\Model\Data\Value\ValueClassThatDoesNotExist', 'value' => ['value1']],
            ],
            'configuration' => [],
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->subject->convertJobToSubmission($job);
    }

    /**
     * @return array<array{0:SubmissionDataSetInterface,1:JobInterface,2:string}>
     */
    public function hashDataProvider(): array
    {
        $config = $this->createRouteConfig('integration1', 'route1', ['routeId1' => []]);

        return [
            [
                new SubmissionDataSet(
                    ['field1' => 'value1'],
                    $config,
                    ['timestamp' => 1716482226, 'context1' => 'contextValue1']
                ),
                $this->createJob(
                    [
                        'data' => ['field1' => ['type' => 'string', 'value' => 'value1']],
                        'configuration' => $config[0],
                        'context' => ['timestamp' => 1716482226, 'context1' => 'contextValue1'],
                    ],
                    'integration1', 'routeId1'
                ),
                '631413DDE0A9ADD5C495B1E0D72708EC',
            ],
        ];
    }

    /**
     * @dataProvider hashDataProvider
     *
     * @test
     */
    public function getSubmissionHash(SubmissionDataSetInterface $submission, JobInterface $job, string $expectedHash): void
    {
        $hash = $this->subject->getSubmissionHash($submission);
        $this->assertEquals($expectedHash, $hash);
    }

    /**
     * @dataProvider hashDataProvider
     *
     * @test
     */
    public function getJobHash(SubmissionDataSetInterface $submission, JobInterface $job, string $expectedHash): void
    {
        $hash = $this->subject->getJobHash($job);
        $this->assertEquals($expectedHash, $hash);
    }

    /**
     * @throws DigitalMarketingFrameworkException
     *
     * @dataProvider hashDataProvider
     *
     * @test
     */
    public function getSubmissionAndConvertedJobHash(SubmissionDataSetInterface $submission, JobInterface $job, string $expectedHash): void
    {
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
     *
     * @dataProvider hashDataProvider
     *
     * @test
     */
    public function getJobAndConvertedSubmissionHash(SubmissionDataSetInterface $submission, JobInterface $job, string $expectedHash): void
    {
        $jobHash = $this->subject->getJobHash($job);
        $convertedSubmission = $this->subject->convertJobToSubmission($job);
        $convertedSubmissionHash = $this->subject->getSubmissionHash($convertedSubmission);
        $convertedJob = $this->subject->convertSubmissionToJob($convertedSubmission, 'integration1', 'routeId1');
        $convertedJobHash = $this->subject->getJobHash($convertedJob);

        $this->assertEquals($jobHash, $convertedSubmissionHash);
        $this->assertEquals($convertedSubmissionHash, $convertedJobHash);
    }

    /** @test */
    public function getSubmissionLabel(): void
    {
        $submission = new SubmissionDataSet(
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
        $this->assertEquals('B31F5#route1', $label);
    }

    /** @test */
    public function getJobLabel(): void
    {
        $job = $this->createJob([
            'data' => [],
            'configuration' => [
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
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1', 'ABCDEFGHIJKLMNO');
        $label = $this->subject->getJobLabel($job);
        $this->assertEquals('ABCDE#route1', $label);
    }

    /** @test */
    public function getJobLabelWithoutOwnHash(): void
    {
        $job = $this->createJob([
            'data' => [],
            'configuration' => [
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
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $label = $this->subject->getJobLabel($job);
        $this->assertEquals('B31F5#route1', $label);
    }

    /** @test */
    public function getJobRouteId(): void
    {
        $job = $this->createJob([
            'data' => [],
            'configuration' => [
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
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $route = $this->subject->getJobRouteId($job);
        $this->assertEquals('routeId1', $route);
    }

    /** @test */
    public function getJobIntegrationName(): void
    {
        $job = $this->createJob([
            'data' => [],
            'configuration' => [
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
            'context' => ['timestamp' => 1716482226],
        ], 'integration1', 'routeId1');
        $integration = $this->subject->getJobRouteIntegrationName($job);
        $this->assertEquals('integration1', $integration);
    }
}
