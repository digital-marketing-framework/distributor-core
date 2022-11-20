<?php

namespace DigitalMarketingFramework\Distributer\Core\ConfigurationResolver\ContentResolver;

use DigitalMarketingFramework\Core\ConfigurationResolver\ContentResolver\MultiValueContentResolver;
use DigitalMarketingFramework\Core\Model\Data\Value\MultiValueInterface;
use DigitalMarketingFramework\Distributer\Core\Model\Data\Value\DiscreteMultiValue;

class DiscreteMultiValueContentResolver extends MultiValueContentResolver
{
    protected function getMultiValue(): MultiValueInterface
    {
        return new DiscreteMultiValue([]);
    }
}
