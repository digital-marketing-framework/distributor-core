<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\Utility;

use DigitalMarketingFramework\Core\Model\Data\Value\MultiValue;
use DigitalMarketingFramework\Core\Tests\Unit\Utility\GeneralUtilityTest as CoreGeneralUtilityTest;
use DigitalMarketingFramework\Distributor\Core\Model\Data\Value\DiscreteMultiValue;

class GeneralUtilityTest extends CoreGeneralUtilityTest
{
    public function valueIsEmptyProvider(): array
    {
        return [
            [new DiscreteMultiValue(['']), false],
            [new DiscreteMultiValue([]), true],
            [new DiscreteMultiValue([0]), false],
            [new DiscreteMultiValue([1]), false],
            [new DiscreteMultiValue(['0']), false],
            [new DiscreteMultiValue(['1']), false],
            [new DiscreteMultiValue(['value1']), false],
        ];
    }
    public function valueIsTrueProvider(): array
    {
        return [
            [new DiscreteMultiValue([]), false],
            [new DiscreteMultiValue([0]), true],
            [new DiscreteMultiValue([1]), true],
            [new DiscreteMultiValue([5]), true],
            [new DiscreteMultiValue(['']), true],
            [new DiscreteMultiValue(['value1']), true],
        ];
    }

    public function isListProvider(): array
    {
        return [
            [new DiscreteMultiValue(), true],
            [new DiscreteMultiValue(['value1']), true],
        ];
    }

    public function castValueToArrayProvider(): array
    {
        return [
            [new DiscreteMultiValue([]), null, null, []],
            [new DiscreteMultiValue(['value1', 'value2']), null, null, ['value1', 'value2']],
            [new DiscreteMultiValue([' value1', 'value2 ']), null, null, ['value1', 'value2']],
        ];
    }

    public function compareListsProvider(): array
    {
        // values in one group are considered to be equal
        $valueGroups = [
            [new DiscreteMultiValue(), '',],
            [new DiscreteMultiValue(['value1'])],
            [new DiscreteMultiValue(['value2'])],

            [new DiscreteMultiValue(['value1', 'value2']), new DiscreteMultiValue(['value2', 'value1']), 'value1,value2'],
            [new DiscreteMultiValue([5,7,13]), new DiscreteMultiValue([13,7,5]), '5,7,13',],

            [new MultiValue(['value101', 'value102']), new DiscreteMultiValue(['value102', 'value101']), 'value101,value102'],
            [new MultiValue([105,107,113]), new DiscreteMultiValue([113,107,105]), '105,107,113',],

            [new DiscreteMultiValue(['value201', 'value202']), new MultiValue(['value202', 'value201']), 'value201,value202'],
            [new DiscreteMultiValue([205,207,213]), new MultiValue([213,207,205]), '205,207,213',],
        ];
        return $this->generateComparisonPairs($valueGroups);
    }
}
