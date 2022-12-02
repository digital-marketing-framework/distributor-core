<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProvider;

use DigitalMarketingFramework\Distributor\Core\DataProvider\TimestampDataProvider;

class TimestampDataProviderTest extends AbstractDataProviderTest
{
    protected const DATA_PROVIDER_CLASS = TimestampDataProvider::class;

    /** @test */
    public function doesNotDoAnythingIfDisabled(): void
    {
        $this->setDataProviderConfiguration(['enabled' => false]);
        $this->globalContext->expects($this->never())->method('getTimestamp');

        $this->createDataProvider();

        $this->subject->addContext($this->globalContext);
        $this->assertEmpty($this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    /** @test */
    public function doesNotDoAnythingIfTimestampIsNotAvailable(): void
    {
        $this->setDataProviderConfiguration(['enabled' => true]);
        $this->globalContext->expects($this->once())->method('getTimestamp')->willReturn(null);

        $this->createDataProvider();

        $this->subject->addContext($this->globalContext);
        $this->assertEmpty($this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    /** @test */
    public function timestampIsAddedToContextAndFields(): void
    {
        $this->setDataProviderConfiguration(['enabled' => true]);
        $this->globalContext->expects($this->once())->method('getTimestamp')->willReturn(1669119788);

        $this->createDataProvider();

        $this->subject->addContext($this->globalContext);
        $this->assertEquals(['timestamp' => 1669119788], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'timestamp' => '2022-11-22T12:23:08+00:00',
        ], $this->submissionData->toArray());
    }

    /** @test */
    public function customFormFieldCanBeUsed(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'field' => 'custom_timestamp_field',
        ]);
        $this->globalContext->expects($this->once())->method('getTimestamp')->willReturn(1669119788);

        $this->createDataProvider();
        
        $this->subject->addContext($this->globalContext);
        $this->assertEquals(['timestamp' => 1669119788], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'custom_timestamp_field' => '2022-11-22T12:23:08+00:00',
        ], $this->submissionData->toArray());
    }

    /** @test */
    public function customFormatCanBeUsed(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'format' => 'Y-m-d',
        ]);
        $this->globalContext->expects($this->once())->method('getTimestamp')->willReturn(1669119788);

        $this->createDataProvider();
        
        $this->subject->addContext($this->globalContext);
        $this->assertEquals(['timestamp' => 1669119788], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'timestamp' => '2022-11-22',
        ], $this->submissionData->toArray());
    }

    /** @test */
    public function doesNotOverwriteFieldByDefault(): void
    {
        $this->setDataProviderConfiguration(['enabled' => true]);
        $this->globalContext->expects($this->once())->method('getTimestamp')->willReturn(1669119788);
        $this->submissionData['timestamp'] = 'timestampFromFormData';

        $this->createDataProvider();

        $this->subject->addContext($this->globalContext);
        $this->assertEquals(['timestamp' => 1669119788], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'timestamp' => 'timestampFromFormData',
        ], $this->submissionData->toArray());
    }
}
