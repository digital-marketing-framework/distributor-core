<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration\Factory;

use DigitalMarketingFramework\Core\ConfigurationDocument\ConfigurationDocumentManagerInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\Value\FileValue;
use DigitalMarketingFramework\Core\Model\Data\Value\MultiValue;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Core\Model\File\FileInterface;
use DigitalMarketingFramework\Core\Model\Queue\Job;
use DigitalMarketingFramework\Core\Tests\ListMapTestTrait;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributor\Core\Model\Data\Value\DiscreteMultiValue;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory
 */
class QueueDataFactoryTest extends TestCase
{
    use ListMapTestTrait;

    protected ConfigurationDocumentManagerInterface&MockObject $configurationDocumentManager;

    protected QueueDataFactory $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurationDocumentManager = $this->createMock(ConfigurationDocumentManagerInterface::class);
        $this->configurationDocumentManager->method('getConfigurationStackFromConfiguration')->willReturnCallback(static function (array $configuration) {
            return [$configuration];
        });
        $this->subject = new QueueDataFactory($this->configurationDocumentManager);
    }

    /**
     * @return array<string>
     */
    protected function routeIdProvider(): array
    {
        return [
            'routeId1',
            'routeId2',
        ];
    }

    /**
     * @return array<array{0:array<string,string|ValueInterface>,1:array<string,array{type:string,value:mixed}>}>
     */
    protected function packDataProvider(): array
    {
        $file = $this->createMock(FileInterface::class);
        $file->method('getName')->willReturn('name1');
        $file->method('getPublicUrl')->willReturn('url1');
        $file->method('getRelativePath')->willReturn('path1');
        $file->method('getMimeType')->willReturn('type1');

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
                    'field7' => new FileValue($file),
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
    protected function packConfigurationProvider(): array
    {
        return [
            [
                [ // config
                    'distributor' => [
                        'routes' => [
                            'routeId1' => $this->createListItem([
                                'type' => 'route1',
                                'pass' => '',
                                'config' => [
                                    'route1' => [
                                        'confKey1' => 'confValue1',
                                    ],
                                ],
                            ], 'routeId1', 10),
                            'routeId2' => $this->createListItem([
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
                [ // packed config
                    'distributor' => [
                        'routes' => [
                            'routeId1' => $this->createListItem([
                                'type' => 'route1',
                                'pass' => '',
                                'config' => [
                                    'route1' => [
                                        'confKey1' => 'confValue1',
                                    ],
                                ],
                            ], 'routeId1', 10),
                            'routeId2' => $this->createListItem([
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
        ];
    }

    /**
     * @return array<array{0:array<string,mixed>,1:array<string,mixed>}>
     */
    protected function packContextProvider(): array
    {
        return [
            [[], []],
            [['contextKey1' => 'contextValue1'], ['contextKey1' => 'contextValue1']],
        ];
    }

    /**
     * @return array<array{
     *   0:array<string,string|ValueInterface>,
     *   1:array<int,array<string,mixed>>,
     *   2:array<string,mixed>,
     *   3:string,
     *   4:array{
     *     routeId:string,
     *     submission:array{
     *       data:array<string,array{type:string,value:mixed}>,
     *       configuration:array<string,mixed>,
     *       context:array<string,mixed>
     *     }
     *   }
     * }>
     */
    public function packProvider(): array
    {
        $result = [];
        foreach ($this->packDataProvider() as [$data, $packedData]) {
            foreach ($this->packConfigurationProvider() as [$configuration, $packedConfiguration]) {
                foreach ($this->packContextProvider() as [$context, $packedContext]) {
                    foreach ($this->routeIdProvider() as $routeId) {
                        $result[] = [
                            $data,
                            [$configuration],
                            $context,
                            $routeId,
                            [
                                'routeId' => $routeId,
                                'submission' => [
                                    'data' => $packedData,
                                    'configuration' => $packedConfiguration,
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
     * @param array<string,string|ValueInterface> $data
     * @param array<int,array<string,mixed>> $configuration
     * @param array<string,mixed> $context
     * @param array{
     *     routeId:string,
     *     submission:array{
     *       data:array<string,array{type:string,value:mixed}>,
     *       configuration:array<string,mixed>,
     *       context:array<string,mixed>
     *     }
     *   } $jobData
     *
     * @dataProvider packProvider
     *
     * @test
     */
    public function pack(array $data, array $configuration, array $context, string $routeId, array $jobData): void
    {
        $submission = new SubmissionDataSet($data, $configuration, $context);
        $job = $this->subject->convertSubmissionToJob($submission, $routeId);
        $this->assertEquals($jobData, $job->getData());
    }

    /**
     * @param array<string,string|ValueInterface> $data
     * @param array<int,array<string,mixed>> $configuration
     * @param array<string,mixed> $context
     * @param array{
     *     routeId:string,
     *     submission:array{
     *       data:array<string,array{type:string,value:mixed}>,
     *       configuration:array<string,mixed>,
     *       context:array<string,mixed>
     *     }
     *   } $jobData
     *
     * @throws DigitalMarketingFrameworkException
     *
     * @dataProvider packProvider
     *
     * @test
     */
    public function unpack(array $data, array $configuration, array $context, string $routeId, array $jobData): void
    {
        $job = new Job();
        $job->setData($jobData);

        $submission = $this->subject->convertJobToSubmission($job);

        $this->assertEquals($data, $submission->getData()->toArray());
        $this->assertEquals($configuration, $submission->getConfiguration()->toArray());
        $this->assertEquals($context, $submission->getContext()->toArray());
    }

    /**
     * @param array<string,string|ValueInterface> $data
     * @param array<int,array<string,mixed>> $configuration
     * @param array<string,mixed> $context
     * @param array{
     *     routeId:string,
     *     submission:array{
     *       data:array<string,array{type:string,value:mixed}>,
     *       configuration:array<string,mixed>,
     *       context:array<string,mixed>
     *     }
     *   } $jobData
     *
     * @throws DigitalMarketingFrameworkException
     *
     * @dataProvider packProvider
     *
     * @test
     */
    public function packUnpack(array $data, array $configuration, array $context, string $routeId, array $jobData): void
    {
        $submission = new SubmissionDataSet($data, $configuration, $context);
        $job = $this->subject->convertSubmissionToJob($submission, $routeId);
        $this->assertEquals($jobData, $job->getData());

        /** @var SubmissionDataSetInterface $result */
        $result = $this->subject->convertJobToSubmission($job);
        $this->assertEquals($data, $result->getData()->toArray());
        $this->assertEquals($configuration, $result->getConfiguration()->toArray());
        $this->assertEquals($context, $result->getContext()->toArray());
    }
}
