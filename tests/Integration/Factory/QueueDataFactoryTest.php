<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration\Factory;

use DigitalMarketingFramework\Core\ConfigurationDocument\ConfigurationDocumentManagerInterface;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\Value\FileValue;
use DigitalMarketingFramework\Core\Model\Data\Value\MultiValue;
use DigitalMarketingFramework\Core\Model\File\FileInterface;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Core\Model\Queue\Job;
use DigitalMarketingFramework\Distributor\Core\Model\Data\Value\DiscreteMultiValue;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers QueueDataFactory
 */
class QueueDataFactoryTest extends TestCase
{
    protected ConfigurationDocumentManagerInterface&MockObject $configurationDocumentManager;

    protected QueueDataFactory $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurationDocumentManager = $this->createMock(ConfigurationDocumentManagerInterface::class);
        $this->configurationDocumentManager->method('getConfigurationStackFromConfiguration')->willReturnCallback(function(array $configuration) {
            return [$configuration];
        });
        $this->subject = new QueueDataFactory($this->configurationDocumentManager);
    }

    protected function routePassProvider(): array
    {
        return [
            ['route1', 0],
            ['route2', 5],
        ];
    }

    protected function packDataProvider(): array
    {
        $file = $this->createMock(FileInterface::class);
        $file->method('getName')->willReturn($arguments[0] ?? 'name1');
        $file->method('getPublicUrl')->willReturn($arguments[1] ?? 'url1');
        $file->method('getRelativePath')->willReturn($arguments[2] ?? 'path1');
        $file->method('getMimeType')->willReturn($arguments[3] ?? 'type1');
        return [
            [[], []],
            [
                [
                    'field1' => 'value1',
                    'field2' => 'value2',
                    'field3' => new MultiValue(),
                    'field4' => new MultiValue([5, 7, 17]),
                    'field5' => new DiscreteMultiValue(),
                    'field6' => new DiscreteMultiValue([5, 7, 17]),
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

    protected function packConfigurationProvider(): array
    {
        return [
            [[], []],
            [['confKey1' => 'confValue1'], ['confKey1' => 'confValue1']],
        ];
    }

    protected function packContextProvider(): array
    {
        return [
            [[], []],
            [['contextKey1' => 'contextValue1'], ['contextKey1' => 'contextValue1']],
        ];
    }


    public function packProvider(): array
    {
        $result = [];
        foreach ($this->packDataProvider() as list($data, $packedData)) {
            foreach ($this->packConfigurationProvider() as list($configuration, $packedConfiguration)) {
                foreach ($this->packContextProvider() as list($context, $packedContext)) {
                    foreach ($this->routePassProvider() as list($route, $pass)) {
                        $result[] = [
                            $data,
                            [$configuration],
                            $context,
                            $route,
                            $pass,
                            [
                                'route' => $route,
                                'pass' => $pass,
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
     * @dataProvider packProvider
     * @test
     */
    public function pack(array $data, array $configuration, array $context, string $route, int $pass, array $jobData): void
    {
        $submission = new SubmissionDataSet($data, $configuration, $context);
        $job = $this->subject->convertSubmissionToJob($submission, $route, $pass);
        $this->assertEquals($jobData, $job->getData());
    }

    /**
     * @throws DigitalMarketingFrameworkException
     * @dataProvider packProvider
     * @test
     */
    public function unpack(array $data, array $configuration, array $context, string $route, int $pass, array $jobData): void
    {
        $job = new Job();
        $job->setData($jobData);
        $submission = $this->subject->convertJobToSubmission($job);

        $this->assertEquals($data, $submission->getData()->toArray());
        $this->assertEquals($configuration, $submission->getConfiguration()->toArray());
        $this->assertEquals($context, $submission->getContext()->toArray());
    }

    /**
     * @throws DigitalMarketingFrameworkException
     * @dataProvider packProvider
     * @test
     */
    public function packUnpack(array $data, array $configuration, array $context, string $route, int $pass, array $jobData): void
    {
        $submission = new SubmissionDataSet($data, $configuration, $context);
        $job = $this->subject->convertSubmissionToJob($submission, $route, $pass);
        $this->assertEquals($jobData, $job->getData());

        /** @var SubmissionDataSetInterface $result */
        $result = $this->subject->convertJobToSubmission($job);
        $this->assertEquals($data, $result->getData()->toArray());
        $this->assertEquals($configuration, $result->getConfiguration()->toArray());
        $this->assertEquals($context, $result->getContext()->toArray());
    }
}
