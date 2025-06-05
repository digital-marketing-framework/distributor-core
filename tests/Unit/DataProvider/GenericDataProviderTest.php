<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProvider;

use DigitalMarketingFramework\Distributor\Core\Tests\DataProvider\GenericDataProvider;
use PHPUnit\Framework\Attributes\Test;

class GenericDataProviderTest extends DataProviderTestBase
{
    protected const DATA_PROVIDER_CLASS = GenericDataProvider::class;

    /**
     * @param array<string,mixed> $contextToAdd
     * @param array<string,mixed> $fieldsToAdd
     */
    protected function createGenericDataProvider(string $keyword = 'myCustomKeyword', array $contextToAdd = [], array $fieldsToAdd = []): void
    {
        $this->createDataProvider($keyword, [$contextToAdd, $fieldsToAdd]);
    }

    #[Test]
    public function disabledDataProviderDoesNotDoAnything(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => false,
        ]);

        $this->createGenericDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    #[Test]
    public function enabledDataProviderAddsFieldsToContextAndToData(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
        ]);

        $this->createGenericDataProvider(
            contextToAdd: [
                'contextField1' => 'contextValue1',
            ],
            fieldsToAdd: [
                'field1' => 'value1',
            ]
        );

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore + [
            'contextField1' => 'contextValue1',
        ], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'field1' => 'value1',
        ], $this->submissionData->toArray());
    }

    #[Test]
    public function enabledDataProviderWillNotOverwriteFieldsByDefault(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
        ]);

        $this->submissionData['field1'] = 'value1';

        $this->createGenericDataProvider(
            fieldsToAdd: [
                'field1' => 'value1b',
                'field2' => 'value2b',
            ]
        );

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'field1' => 'value1',
            'field2' => 'value2b',
        ], $this->submissionData->toArray());
    }

    #[Test]
    public function enabledDataProviderWillOverwriteEmptyFieldsByDefault(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
        ]);

        $this->submissionData['field1'] = '';

        $this->createGenericDataProvider(
            fieldsToAdd: [
                'field1' => 'value1b',
                'field2' => 'value2b',
            ]
        );

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'field1' => 'value1b',
            'field2' => 'value2b',
        ], $this->submissionData->toArray());
    }

    #[Test]
    public function enabledDataProviderWillOverwriteFieldsIfConfiguredThusly(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'mustBeEmpty' => false,
        ]);

        $this->submissionData['field1'] = 'value1';

        $this->createGenericDataProvider(
            fieldsToAdd: [
                'field1' => 'value1b',
                'field2' => 'value2b',
            ]
        );

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'field1' => 'value1b',
            'field2' => 'value2b',
        ], $this->submissionData->toArray());
    }

    #[Test]
    public function enabledDataProviderWillAddNonExistentFieldsByDefault(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
        ]);

        $this->createGenericDataProvider(
            fieldsToAdd: [
                'field1' => 'value1',
            ]
        );

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'field1' => 'value1',
        ], $this->submissionData->toArray());
    }

    #[Test]
    public function enabledDataProviderWillNotAddNonExistentFieldsIfConfiguredThusly(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'mustExist' => true,
        ]);

        $this->createGenericDataProvider(
            fieldsToAdd: [
                'field1' => 'value1',
            ]
        );

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    #[Test]
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
            fieldsToAdd: [
                'field1' => 'value1b',
                'field2' => 'value2b',
                'field3' => 'value3b',
            ]
        );

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'field1' => 'value1b',
            'field2' => 'value2b',
            'field3' => 'value3b',
        ], $this->submissionData->toArray());
    }
}
