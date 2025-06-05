<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProvider;

use DigitalMarketingFramework\Distributor\Core\DataProvider\RequestVariablesDataProvider;
use PHPUnit\Framework\Attributes\Test;

class RequestVariablesDataProviderTest extends DataProviderTestBase
{
    protected const DATA_PROVIDER_CLASS = RequestVariablesDataProvider::class;

    protected const DEFAULT_CONFIG = parent::DEFAULT_CONFIG + [
        RequestVariablesDataProvider::KEY_VARIABLE_FIELD_MAP => [],
    ];

    #[Test]
    public function doesNotDoAnythingIfDisabled(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => false,
        ]);
        $this->globalContext->expects($this->never())->method('getRequestVariable');

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    #[Test]
    public function doesNotDoAnythingIfCookieIsNotAvailable(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'variableFieldMap' => [
                'requestVariableItemId1' => $this->createMapItem('requestVariableName1', 'fieldName1', 'requestVariableItemId1', 10),
            ],
        ]);
        $this->globalContext->expects($this->exactly(2))->method('getRequestVariable')->willReturn(null);

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore, $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEmpty($this->submissionData->toArray());
    }

    #[Test]
    public function requestVariablesAreAddedToContextAndFields(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'variableFieldMap' => [
                'requestVariableItemId1' => $this->createMapItem('requestVariableName1', 'fieldName1', 'requestVariableItemId1', 10),
                'requestVariableItemId2' => $this->createMapItem('requestVariableName2', 'fieldName2', 'requestVariableItemId2', 20),
            ],
        ]);
        $this->globalContext->expects($this->any())->method('getRequestVariable')->willReturnMap([
            ['requestVariableName1', 'requestVariableValue1'],
            ['requestVariableName2', 'requestVariableValue2'],
        ]);

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore + [
            'request_variables' => [
                'requestVariableName1' => 'requestVariableValue1',
                'requestVariableName2' => 'requestVariableValue2',
            ],
        ], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'fieldName1' => 'requestVariableValue1',
            'fieldName2' => 'requestVariableValue2',
        ], $this->submissionData->toArray());
    }

    #[Test]
    public function doesNotOverwriteFieldByDefault(): void
    {
        $this->setDataProviderConfiguration([
            'enabled' => true,
            'variableFieldMap' => [
                'requestVariableItemId1' => $this->createMapItem('requestVariableName1', 'fieldName1', 'requestVariableItemId1', 10),
            ],
        ]);
        $this->globalContext->expects($this->exactly(2))->method('getRequestVariable')->willReturnMap([
            ['requestVariableName1', 'requestVariableValue1'],
        ]);
        $this->submissionData['fieldName1'] = 'requestVariableValue1FromFormData';

        $this->createDataProvider();

        $contextBefore = $this->submissionContext->toArray();
        $this->subject->addContext($this->submissionContext);
        $this->assertEquals($contextBefore + [
            'request_variables' => [
                'requestVariableName1' => 'requestVariableValue1',
            ],
        ], $this->submissionContext->toArray());

        $this->subject->addData();
        $this->assertEquals([
            'fieldName1' => 'requestVariableValue1FromFormData',
        ], $this->submissionData->toArray());
    }
}
