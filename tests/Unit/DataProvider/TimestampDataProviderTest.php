<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProvider;

use DigitalMarketingFramework\Distributor\Core\DataProvider\TimestampDataProvider;
use PHPUnit\Framework\Attributes\Test;

class TimestampDataProviderTest extends DataProviderTestBase
{
    protected const DATA_PROVIDER_CLASS = TimestampDataProvider::class;

    protected const DEFAULT_CONFIG = parent::DEFAULT_CONFIG + [
        TimestampDataProvider::KEY_FIELD => TimestampDataProvider::DEFAULT_FIELD,
        TimestampDataProvider::KEY_FORMAT => TimestampDataProvider::DEFAULT_FORMAT,
    ];

    private string $originalTimezone;

    protected function setUp(): void
    {
        // Save original timezone and set to UTC for consistent test results
        $this->originalTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        parent::setUp();
    }

    protected function tearDown(): void
    {
        // Restore original timezone
        date_default_timezone_set($this->originalTimezone);
        parent::tearDown();
    }

    #[Test]
    public function addsContextEvenIfDisabled(): void
    {
        $this->setDataProviderConfiguration(['enabled' => false]);
        $this->globalContext->expects($this->once())->method('getTimestamp')->willReturn(1669119788);

        $this->createDataProvider();

        $this->subject->addContext($this->submissionContext);
        $this->assertEquals(['timestamp' => 1669119788], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    #[Test]
    public function doesNotDoAnythingIfTimestampIsNotAvailable(): void
    {
        $this->setDataProviderConfiguration(['enabled' => true]);
        $this->globalContext->expects($this->exactly(2))->method('getTimestamp')->willReturn(null);

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    #[Test]
    public function timestampIsAddedToContextAndFields(): void
    {
        $this->setDataProviderConfiguration(['enabled' => true]);
        $this->globalContext->expects($this->exactly(2))->method('getTimestamp')->willReturn(1669119788);

        $this->createDataProvider();

        $this->subject->addContext($this->submissionContext);
        $this->assertEquals(['timestamp' => 1669119788], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'timestamp' => '2022-11-22T12:23:08+00:00',
        ], $this->submissionData->toArray());
    }

    #[Test]
    public function customFormFieldCanBeUsed(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'field' => 'custom_timestamp_field',
        ]);
        $this->globalContext->expects($this->exactly(2))->method('getTimestamp')->willReturn(1669119788);

        $this->createDataProvider();

        $this->subject->addContext($this->submissionContext);
        $this->assertEquals(['timestamp' => 1669119788], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'custom_timestamp_field' => '2022-11-22T12:23:08+00:00',
        ], $this->submissionData->toArray());
    }

    #[Test]
    public function customFormatCanBeUsed(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'format' => 'Y-m-d',
        ]);
        $this->globalContext->expects($this->exactly(2))->method('getTimestamp')->willReturn(1669119788);

        $this->createDataProvider();

        $this->subject->addContext($this->submissionContext);
        $this->assertEquals(['timestamp' => 1669119788], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'timestamp' => '2022-11-22',
        ], $this->submissionData->toArray());
    }

    #[Test]
    public function doesNotOverwriteFieldByDefault(): void
    {
        $this->setDataProviderConfiguration(['enabled' => true]);
        $this->globalContext->expects($this->exactly(2))->method('getTimestamp')->willReturn(1669119788);
        $this->submissionData['timestamp'] = 'timestampFromFormData';

        $this->createDataProvider();

        $this->subject->addContext($this->submissionContext);
        $this->assertEquals(['timestamp' => 1669119788], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'timestamp' => 'timestampFromFormData',
        ], $this->submissionData->toArray());
    }
}
