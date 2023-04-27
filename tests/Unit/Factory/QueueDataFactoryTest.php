<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\Factory;

use DigitalMarketingFramework\Core\ConfigurationDocument\ConfigurationDocumentManagerInterface;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Model\Queue\Job;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Tests\Model\Data\Value\InvalidValue;
use DigitalMarketingFramework\Distributor\Core\Tests\Model\Data\Value\StringValue;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers QueueDataFactory
 */
class QueueDataFactoryTest extends TestCase
{
    protected QueueDataFactory $subject;

    protected DataInterface&MockObject $submissionData;

    protected SubmissionConfigurationInterface&MockObject $submissionConfiguration;

    protected ContextInterface&MockObject $submissionContext;

    protected ConfigurationDocumentManagerInterface&MockObject $configurationDocumentManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationDocumentManager = $this->createMock(ConfigurationDocumentManagerInterface::class);
        $this->configurationDocumentManager->method('getConfigurationStackFromConfiguration')->willReturnCallback(function($configuration) {
            return [$configuration];
        });

        $this->subject = new QueueDataFactory($this->configurationDocumentManager);
    }

    protected function createRouteConfig(string $routeName, int $passCount = 1): array
    {
        $config = [
            'routes' => [
                $routeName => [],
            ],
        ];
        if ($passCount > 1) {
            $config['routes'][$routeName]['passes'] = array_fill(0, $passCount, []);
        }
        return [$config];
    }

    /** @test */
    public function convertSubmissionWithStringValueToJob(): void
    {
        $data = [
            'field1' => 'value1',
        ];
        $configuration = $this->createRouteConfig('route1', 6);
        $submission = new SubmissionDataSet($data, $configuration);
        $job = $this->subject->convertSubmissionToJob($submission, 'route1', 5);
        $this->assertEquals([
            'route' => 'route1',
            'pass' => 5,
            'submission' => [
                'data' => [
                    'field1' => ['type' => 'string', 'value' => 'value1'],
                ],
                'configuration' => $configuration[0],
                'context' => [],
            ],
        ], $job->getData());
        $this->assertEquals('1F6F7D0082DE10270D0193DA6E1C7F63', $job->getHash());
        $this->assertEquals('1F6F7#route1#6', $job->getLabel());
    }

    /** @test */
    public function convertSubmissionWithComplexFieldToJob(): void
    {
        $data = [
            'field1' => new StringValue('value1'),
        ];
        $configuration = $this->createRouteConfig('route1', 6);
        $submission = new SubmissionDataSet($data, $configuration);
        $job = $this->subject->convertSubmissionToJob($submission, 'route1', 5);
        $this->assertEquals([
            'route' => 'route1',
            'pass' => 5,
            'submission' => [
                'data' => [
                    'field1' => ['type' => StringValue::class, 'value' => ['value1']],
                ],
                'configuration' => $configuration[0],
                'context' => [],
            ],
        ], $job->getData());
        $this->assertEquals('3ECF19772F0DC7C7DD192635FCB37AF6', $job->getHash());
        $this->assertEquals('3ECF1#route1#6', $job->getLabel());
    }

    /** @test */
    public function convertSubmissionWithInvalidValueToJob(): void
    {
        $data = [
            'field1' => new InvalidValue(),
        ];
        $configuration = $this->createRouteConfig('route1', 6);
        $submission = new SubmissionDataSet($data, $configuration);

        $this->expectException(InvalidArgumentException::class);
        $this->subject->convertSubmissionToJob($submission, 'route1', 5);
    }

    /** @test */
    public function convertSubmissionWithDataConfigurationAndContextToJob(): void
    {
        $data = [
            'field1' => 'value1',
        ];
        $configuration = $this->createRouteConfig('route1');
        $configuration[0] += [
            'globalConfKey1' => 'globalConfValue1',
            'globalConfKey2' => [
                'globalConfKey2.1' => 'globalConfValue2.1',
                'globalConfKey2.2' => 'globalConfValue2.2',
            ],
        ];
        $context = [
            'contextKey1' => 'contextValue1',
            'contextKey2' => [
                'contextKey2.1' => 'contextValue2.1',
                'contextKey2.2' => 'contextValue2.2',
            ]
        ];
        $submission = new SubmissionDataSet($data, $configuration, $context);

        $job = $this->subject->convertSubmissionToJob($submission, 'route1', 0);
        $this->assertEquals([
            'route' => 'route1',
            'pass' => 0,
            'submission' => [
                'data' => [
                    'field1' => ['type' => 'string', 'value' => 'value1'],
                ],
                'configuration' => $configuration[0],
                'context' => $context,
            ],
        ], $job->getData());
        $this->assertEquals('D2323A197F09464AD6181B1BE6BEB411', $job->getHash());
        $this->assertEquals('D2323#route1', $job->getLabel());
    }

    protected function createJob(array $submissionData, string $route, int $pass = 0, string $hash = ''): JobInterface
    {
        return new Job(
            data:[
                'route' => $route,
                'pass' => $pass,
                'submission' => $submissionData,
            ],
            hash:$hash
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
            'context' => [],
        ], 'route1', 0);
        $submission = $this->subject->convertJobToSubmission($job);
        $this->assertTrue($submission->getData()->fieldExists('field1'));
        $this->assertEquals('value1', $submission->getData()['field1']);
    }

    /** @test */
    public function convertJobWithComplexFieldToSubmission(): void
    {
        $job = $this->createJob([
            'data' => [
                'field1' => ['type' => StringValue::class, 'value' => ['value1']],
            ],
            'configuration' => [],
            'context' => [],
        ], 'route1', 0);
        $submission = $this->subject->convertJobToSubmission($job);
        $this->assertTrue($submission->getData()->fieldExists('field1'));
        $this->assertInstanceOf(StringValue::class, $submission->getData()['field1']);
        $this->assertEquals('value1', (string)$submission->getData()['field1']);
        $this->assertEquals(['value1'], $submission->getData()['field1']->pack());
    }

    /** @test */
    public function convertJobWithInvalidValueToSubmission(): void
    {
        $job = $this->createJob([
            'data' => [
                'field1' => ['type' => InvalidValue::class, 'value' => ['value1']],
            ],
            'configuration' => [],
            'context' => [],
        ], 'route1', 0);
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
            'context' => [],
        ], 'route1', 0);
        $this->expectException(DigitalMarketingFrameworkException::class);
        $this->subject->convertJobToSubmission($job);
    }

    public function hashDataProvider(): array
    {
        return [
            [
                new SubmissionDataSet(
                    ['field1' => 'value1',],
                    [
                        ['conf1' => 'confValue1',],
                    ],
                    ['context1' => 'contextValue1',]
                ),
                $this->createJob(
                    [
                        'data' => ['field1' => ['type' => 'string', 'value' => 'value1']],
                        'configuration' => [
                            'conf1' => 'confValue1',
                        ],
                        'context' => ['context1' => 'contextValue1',]
                    ],
                    'route1',
                    0
                ),
                'ED477ABB7C729B515486967A71C87447'
            ],
        ];
    }

    /**
     * @dataProvider hashDataProvider
     * @test
     */
    public function getSubmissionHash(SubmissionDataSetInterface $submission, JobInterface $job, string $expectedHash): void
    {
        $hash = $this->subject->getSubmissionHash($submission);
        $this->assertEquals($expectedHash, $hash);
    }

    /**
     * @dataProvider hashDataProvider
     * @test
     */
    public function getJobHash(SubmissionDataSetInterface $submission, JobInterface $job, string $expectedHash): void
    {
        $hash = $this->subject->getJobHash($job);
        $this->assertEquals($expectedHash, $hash);
    }

    /**
     * @throws DigitalMarketingFrameworkException
     * @dataProvider hashDataProvider
     * @test
     */
    public function getSubmissionAndConvertedJobHash(SubmissionDataSetInterface $submission, JobInterface $job, string $expectedHash): void
    {
        $submissionHash = $this->subject->getSubmissionHash($submission);
        $convertedJob = $this->subject->convertSubmissionToJob($submission, 'route1', 0);
        $convertedJobHash = $this->subject->getJobHash($convertedJob);
        $convertedSubmission = $this->subject->convertJobToSubmission($convertedJob);
        $convertedSubmissionHash = $this->subject->getSubmissionHash($convertedSubmission);

        $this->assertEquals($submissionHash, $convertedJobHash);
        $this->assertEquals($convertedJobHash, $convertedSubmissionHash);
    }

    /**
     * @throws DigitalMarketingFrameworkException
     * @dataProvider hashDataProvider
     * @test
     */
    public function getJobAndConvertedSubmissionHash(SubmissionDataSetInterface $submission, JobInterface $job, string $expectedHash): void
    {
        $jobHash = $this->subject->getJobHash($job);
        $convertedSubmission = $this->subject->convertJobToSubmission($job);
        $convertedSubmissionHash = $this->subject->getSubmissionHash($convertedSubmission);
        $convertedJob = $this->subject->convertSubmissionToJob($convertedSubmission, 'route1', 0);
        $convertedJobHash = $this->subject->getJobHash($convertedJob);

        $this->assertEquals($jobHash, $convertedSubmissionHash);
        $this->assertEquals($convertedSubmissionHash, $convertedJobHash);
    }

    /** @test */
    public function getSubmissionLabel(): void
    {
        $submission = new SubmissionDataSet([], [['routes' => ['route1' => []]]]);
        $label = $this->subject->getSubmissionLabel($submission, 'route1', 0);
        $this->assertEquals('93031#route1', $label);
    }

    /** @test */
    public function getJobLabel(): void
    {
        $job = $this->createJob([
            'data' => [],
            'configuration' => [[]],
            'context' => [],
        ], 'route1', 0, 'ABCDEFGHIJKLMNO');
        $label = $this->subject->getJobLabel($job);
        $this->assertEquals('ABCDE#route1', $label);
    }

    /** @test */
    public function getJobLabelWithoutOwnHash(): void
    {
        $job = $this->createJob([
            'data' => [],
            'configuration' => [[]],
            'context' => [],
        ], 'route1', 0);
        $label = $this->subject->getJobLabel($job);
        $this->assertEquals('AD83E#route1', $label);
    }

    /** @test */
    public function getJobRoute(): void
    {
        $job = $this->createJob([
            'data' => [],
            'configuration' => [[]],
            'context' => [],
        ], 'route1', 5);
        $route = $this->subject->getJobRoute($job);
        $this->assertEquals('route1', $route);
    }

    /** @test */
    public function getJobRoutePass(): void
    {
        $job = $this->createJob([
            'data' => [],
            'configuration' => [[]],
            'context' => [],
        ], 'route1', 5);
        $pass = $this->subject->getJobRoutePass($job);
        $this->assertEquals(5, $pass);
    }
}
