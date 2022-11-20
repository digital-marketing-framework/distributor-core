<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\Unit\Factory;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Context\ContextInterface;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Model\Queue\Job;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Distributer\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributer\Core\Model\Configuration\SubmissionConfigurationInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributer\Core\Tests\Model\Data\Value\InvalidValue;
use DigitalMarketingFramework\Distributer\Core\Tests\Model\Data\Value\StringValue;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new QueueDataFactory();
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
                'configuration' => $configuration,
                'context' => [],
            ],
        ], $job->getData());
        $this->assertEquals('6A91A75B0A1EB9D023EC7120538ED1EF', $job->getHash());
        $this->assertEquals('6A91A#route1#6', $job->getLabel());
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
                'configuration' => $configuration,
                'context' => [],
            ],
        ], $job->getData());
        $this->assertEquals('5CACCF71524AEC083D728E46F72131B6', $job->getHash());
        $this->assertEquals('5CACC#route1#6', $job->getLabel());
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
                'configuration' => $configuration,
                'context' => $context,
            ],
        ], $job->getData());
        $this->assertEquals('99C96D6D47E55CE1A99D73EE99728E29', $job->getHash());
        $this->assertEquals('99C96#route1', $job->getLabel());
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
                'field1' => ['type' => 'DigitalMarketingFramework\Distributer\Core\Model\Data\Value\ValueClassThatDoesNotExist', 'value' => ['value1']],
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
                        ['conf1' => 'confValue1b',],
                    ],
                    ['context1' => 'contextValue1',]
                ),
                $this->createJob(
                    [
                        'data' => ['field1' => ['type' => 'string', 'value' => 'value1']],
                        'configuration' => [
                            ['conf1' => 'confValue1',],
                            ['conf1' => 'confValue1b',],
                        ],
                        'context' => ['context1' => 'contextValue1',]
                    ],
                    'route1',
                    0
                ),
                '23D25FE3588D5FE0394DC26C5247D294'
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
        $this->assertEquals('4CE97#route1', $label);
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

    public function getSubmissionCacheKeyProvider(): array
    {
        return [
            [
                [],
                [[]],
                [],
                'a:3:{s:4:"data";a:0:{}s:13:"configuration";a:1:{i:0;a:0:{}}s:7:"context";a:0:{}}'
            ],
            [
                [
                    'field1' => 'value1',
                    'field2' => new StringValue('value2')
                ],
                [
                    [
                        'conf1' => 'confValue1',
                        'conf2' => 'confValue2',
                    ]
                ],
                [
                    'ctx1' => 'ctxValue1',
                    'ctx2' => 'ctxValue2',
                ],
                'a:3:{s:4:"data";a:2:{s:6:"field1";a:2:{s:4:"type";s:6:"string";s:5:"value";s:6:"value1";}s:6:"field2";a:2:{s:4:"type";s:77:"DigitalMarketingFramework\Distributer\Core\Tests\Model\Data\Value\StringValue";s:5:"value";a:1:{i:0;s:6:"value2";}}}s:13:"configuration";a:1:{i:0;a:2:{s:5:"conf1";s:10:"confValue1";s:5:"conf2";s:10:"confValue2";}}s:7:"context";a:2:{s:4:"ctx1";s:9:"ctxValue1";s:4:"ctx2";s:9:"ctxValue2";}}'
            ]
        ];
    }

    /**
     * @dataProvider getSubmissionCacheKeyProvider
     * @test
     */
    public function getSubmissionCacheKey(array $data, array $configuration, array $context, string $expectedCacheKey): void
    {
        $submission = new SubmissionDataSet($data, $configuration, $context);
        $cacheKey = $this->subject->getSubmissionCacheKey($submission);
        $this->assertEquals($expectedCacheKey, $cacheKey);
    }
}
