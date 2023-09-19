<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Unit\DataProcessor\ValueSource;

use DigitalMarketingFramework\Core\Tests\Unit\DataProcessor\ValueSource\MultiValueValueSourceTest;
use DigitalMarketingFramework\Distributor\Core\DataProcessor\ValueSource\DiscreteMultiValueValueSource;
use DigitalMarketingFramework\Distributor\Core\Model\Data\Value\DiscreteMultiValue;

class DiscreteMultiValueValueSourceTest extends MultiValueValueSourceTest
{
    protected const CLASS_NAME = DiscreteMultiValueValueSource::class;

    protected const KEYWORD = 'discreteMultiValue';

    protected const MULTI_VALUE_CLASS_NAME = DiscreteMultiValue::class;
}
