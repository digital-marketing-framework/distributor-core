<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProvider;

use DigitalMarketingFramework\Distributor\Core\DataProvider\CookieDataProvider;

class CookieDataProviderTest extends AbstractDataProviderTest
{
    protected const DATA_PROVIDER_CLASS = CookieDataProvider::class;

    protected const DEFAULT_CONFIG = parent::DEFAULT_CONFIG + [
        CookieDataProvider::KEY_COOKIE_FIELD_MAP => CookieDataProvider::DEFAULT_COOKIE_FIELD_MAP,
    ];

    /** @test */
    public function doesNotDoAnythingIfDisabled(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => false,
        ]);
        $this->globalContext->expects($this->never())->method('getCookie');

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    /** @test */
    public function doesNotDoAnythingIfCookieIsNotAvailable(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'cookieFieldMap' => [
                'cookieItemId1' => $this->createMapItem('cookieName1', 'fieldName1', 'cookieItemId1', 10),
            ],
        ]);
        $this->globalContext->expects($this->exactly(2))->method('getCookie')->willReturn(null);

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    /** @test */
    public function cookiesAreAddedToContextAndFields(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'cookieFieldMap' => [
                'cookieItemId1' => $this->createMapItem('cookieName1', 'fieldName1', 'cookieItemId1', 10),
                'cookieItemId2' => $this->createMapItem('cookieName2', 'fieldName2', 'cookieItemId2', 20),
            ],
        ]);
        $this->globalContext->expects($this->any())->method('getCookie')->willReturnMap([
            ['cookieName1', 'cookieValue1'],
            ['cookieName2', 'cookieValue2'],
        ]);

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore + [
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
                'cookieItemId1' => $this->createMapItem('cookieName1', 'fieldName1', 'cookieItemId1', 10),
            ],
        ]);
        $this->globalContext->expects($this->exactly(2))->method('getCookie')->willReturnMap([
            ['cookieName1', 'cookieValue1'],
        ]);
        $this->submissionData['fieldName1'] = 'cookieValue1FromFormData';

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore + [
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
