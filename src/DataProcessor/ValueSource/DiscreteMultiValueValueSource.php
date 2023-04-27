<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProcessor\ValueSource;

use DigitalMarketingFramework\Core\DataProcessor\ValueSource\MultiValueValueSource;
use DigitalMarketingFramework\Core\Model\Data\Value\MultiValueInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Data\Value\DiscreteMultiValue;

class DiscreteMultiValueValueSource extends MultiValueValueSource
{
    protected function getMultiValue(): MultiValueInterface
    {
        return new DiscreteMultiValue();
    }
}
