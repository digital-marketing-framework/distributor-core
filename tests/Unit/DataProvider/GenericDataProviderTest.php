<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProvider;

use DigitalMarketingFramework\Distributor\Core\Tests\DataProvider\GenericDataProvider;

class GenericDataProviderTest extends AbstractDataProviderTest
{
    protected const DATA_PROVIDER_CLASS = GenericDataProvider::class;

    protected function createGenericDataProvider(string $keyword = 'myCustomKeyword', array $contextToAdd = [], array $fieldsToAdd = []): void
    {
        $this->createDataProvider($keyword, [$contextToAdd, $fieldsToAdd]);
    }

    /** @test */
    public function disabledDataProviderDoesNotDoAnything(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => false,
        ]);

        $this->createGenericDataProvider();

        $this->subject->addContext($this->globalContext);
        $this->assertEmpty($this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    /** @test */
    public function enabledDataProviderAddsFieldsToContextAndToData(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
        ]);

        $this->createGenericDataProvider(
            contextToAdd:[
                'contextField1' => 'contextValue1',
            ],
            fieldsToAdd:[
                'field1' => 'value1',
            ]
        );

        $this->subject->addContext($this->globalContext);
        $this->assertEquals([
            'contextField1' => 'contextValue1',
        ], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'field1' => 'value1',
        ], $this->submissionData->toArray());
    }

    /** @test */
    public function enabledDataProviderWillNotOverwriteFieldsByDefault(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
        ]);

        $this->submissionData['field1'] = 'value1';

        $this->createGenericDataProvider(
            fieldsToAdd:[
                'field1' => 'value1b',
                'field2' => 'value2b',
            ]
        );

        $this->subject->addContext($this->globalContext);
        $this->assertEmpty($this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'field1' => 'value1',
            'field2' => 'value2b',
        ], $this->submissionData->toArray());
    }

    /** @test */
    public function enabledDataProviderWillOverwriteEmptyFieldsByDefault(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
        ]);

        $this->submissionData['field1'] = '';

        $this->createGenericDataProvider(
            fieldsToAdd:[
                'field1' => 'value1b',
                'field2' => 'value2b',
            ]
        );

        $this->subject->addContext($this->globalContext);
        $this->assertEmpty($this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'field1' => 'value1b',
            'field2' => 'value2b',
        ], $this->submissionData->toArray());
    }

    /** @test */
    public function enabledDataProviderWillOverwriteFieldsIfConfiguredThusly(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'mustBeEmpty' => false,
        ]);

        $this->submissionData['field1'] = 'value1';

        $this->createGenericDataProvider(
            fieldsToAdd:[
                'field1' => 'value1b',
                'field2' => 'value2b',
            ]
        );

        $this->subject->addContext($this->globalContext);
        $this->assertEmpty($this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'field1' => 'value1b',
            'field2' => 'value2b',
        ], $this->submissionData->toArray());
    }

    /** @test */
    public function enabledDataProviderWillAddNonExistentFieldsByDefault(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
        ]);

        $this->createGenericDataProvider(
            fieldsToAdd:[
                'field1' => 'value1',
            ]
        );

        $this->subject->addContext($this->globalContext);
        $this->assertEmpty($this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'field1' => 'value1',
        ], $this->submissionData->toArray());
    }

    /** @test */
    public function enabledDataProviderWillNotAddNonExistentFieldsIfConfiguredThusly(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'mustExist' => true,
        ]);

        $this->createGenericDataProvider(
            fieldsToAdd:[
                'field1' => 'value1',
            ]
        );

        $this->subject->addContext($this->globalContext);
        $this->assertEmpty($this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    /** @test */
    public function enabledDataProviderWillOverwriteEverythingIfConfiguredThusly(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'mustExist' => false,
            'mustBeEmpty' => false,
        ]);
        $this->submissionData['field1'] = 'value1';
        $this->submissionData['field2'] = '';

        $this->createGenericDataProvider(
            fieldsToAdd:[
                'field1' => 'value1b',
                'field2' => 'value2b',
                'field3' => 'value3b',
            ]
        );

        $this->subject->addContext($this->globalContext);
        $this->assertEmpty($this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'field1' => 'value1b',
            'field2' => 'value2b',
            'field3' => 'value3b',
        ], $this->submissionData->toArray());
    }
}
