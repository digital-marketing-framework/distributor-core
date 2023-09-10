<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration\DataProcessor\ValueSource;

use DigitalMarketingFramework\Core\Tests\Integration\DataProcessor\ValueSource\MultiValueValueSourceTest;
use DigitalMarketingFramework\Distributor\Core\Model\Data\Value\DiscreteMultiValue;
use DigitalMarketingFramework\Distributor\Core\Tests\Integration\DataProcessor\DataProcessorPluginTestTrait;

class DiscreteMultiValueValueSourceTest extends MultiValueValueSourceTest
{
    use DataProcessorPluginTestTrait;

    protected const KEYWORD = 'discreteMultiValue';

    protected const MULTI_VALUE_CLASS_NAME = DiscreteMultiValue::class;
}
