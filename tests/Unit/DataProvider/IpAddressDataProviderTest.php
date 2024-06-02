<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProvider;

use DigitalMarketingFramework\Distributor\Core\DataProvider\IpAddressDataProvider;

class IpAddressDataProviderTest extends AbstractDataProviderTest
{
    protected const DATA_PROVIDER_CLASS = IpAddressDataProvider::class;

    protected const DEFAULT_CONFIG = parent::DEFAULT_CONFIG + [
        IpAddressDataProvider::KEY_FIELD => IpAddressDataProvider::DEFAULT_FIELD,
    ];

    /** @test */
    public function doesNotDoAnythingIfDisabled(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => false,
        ]);
        $this->globalContext->expects($this->never())->method('getIpAddress');

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    /** @test */
    public function doesNotDoAnythingIfIpAddressIsNotAvailable(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
        ]);
        $this->globalContext->expects($this->exactly(2))->method('getIpAddress')->willReturn(null);

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    /** @test */
    public function ipAddressIsAddedToContextAndFields(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
        ]);
        $this->globalContext->expects($this->exactly(2))->method('getIpAddress')->willReturn('111.222.333.444');

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore + ['ip_address' => '111.222.333.444'], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'ip_address' => '111.222.333.444',
        ], $this->submissionData->toArray());
    }

    /** @test */
    public function customFormFieldCanBeUsed(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'field' => 'custom_ip_address_field',
        ]);
        $this->globalContext->expects($this->exactly(2))->method('getIpAddress')->willReturn('111.222.333.444');

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore + ['ip_address' => '111.222.333.444'], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'custom_ip_address_field' => '111.222.333.444',
        ], $this->submissionData->toArray());
    }

    /** @test */
    public function doesNotOverwriteFieldByDefault(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
        ]);
        $this->globalContext->expects($this->exactly(2))->method('getIpAddress')->willReturn('111.222.333.444');
        $this->submissionData['ip_address'] = 'ipAddressFromFormData';

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore + ['ip_address' => '111.222.333.444'], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'ip_address' => 'ipAddressFromFormData',
        ], $this->submissionData->toArray());
    }
}
