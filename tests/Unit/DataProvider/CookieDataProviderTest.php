<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProvider;

use DigitalMarketingFramework\Distributor\Core\DataProvider\CookieDataProvider;

class CookieDataProviderTest extends AbstractDataProviderTest
{
    protected const DATA_PROVIDER_CLASS = CookieDataProvider::class;

    /** @test */
    public function doesNotDoAnythingIfDisabled(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => false,
        ]);
        $this->globalContext->expects($this->never())->method('getCookie');

        $this->createDataProvider();

        $this->subject->addContext($this->globalContext);
        $this->assertEmpty($this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    /** @test */
    public function doesNotDoAnythingIfCookieIsNotAvailable(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'cookieFieldMap' => [
                'cookieName1' => 'fieldName1',
            ],
        ]);
        $this->globalContext->expects($this->once())->method('getCookie')->willReturn(null);

        $this->createDataProvider();

        $this->subject->addContext($this->globalContext);
        $this->assertEmpty($this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    /** @test */
    public function cookiesAreAddedToContextAndFields(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'cookieFieldMap' => [
                'cookieName1' => 'fieldName1',
                'cookieName2' => 'fieldName2',
            ],
        ]);
        $this->globalContext->expects($this->any())->method('getCookie')->willReturnMap([
            ['cookieName1', 'cookieValue1'],
            ['cookieName2', 'cookieValue2'],
        ]);

        $this->createDataProvider();

        $this->subject->addContext($this->globalContext);
        $this->assertEquals([
            'cookies' => [
                'cookieName1' => 'cookieValue1',
                'cookieName2' => 'cookieValue2',
            ],
        ], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'fieldName1' => 'cookieValue1',
            'fieldName2' => 'cookieValue2',
        ], $this->submissionData->toArray());
    }

    /** @test */
    public function doesNotOverwriteFieldByDefault(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'cookieFieldMap' => [
                'cookieName1' => 'fieldName1',
            ],
        ]);
        $this->globalContext->expects($this->once())->method('getCookie')->willReturnMap([
            ['cookieName1', 'cookieValue1'],
        ]);
        $this->submissionData['fieldName1'] = 'cookieValue1FromFormData';

        $this->createDataProvider();

        $this->subject->addContext($this->globalContext);
        $this->assertEquals([
            'cookies' => [
                'cookieName1' => 'cookieValue1',
            ],
        ], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'fieldName1' => 'cookieValue1FromFormData',
        ], $this->submissionData->toArray());
    }
}
