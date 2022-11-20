<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\Integration\ConfigurationResolver\ContentResolver;

use DigitalMarketingFramework\Core\Tests\Integration\ConfigurationResolver\ContentResolver\MultiValueContentResolverTest;
use DigitalMarketingFramework\Distributer\Core\ConfigurationResolver\ContentResolver\DiscreteMultiValueContentResolver;
use DigitalMarketingFramework\Distributer\Core\Model\Data\Value\DiscreteMultiValue;
use DigitalMarketingFramework\Distributer\Core\Tests\Integration\ConfigurationResolverRegistryTestTrait;

/**
 * @covers DiscreteMultiValueContentResolver
 */
class DiscreteMultiValueContentResolverTest extends MultiValueContentResolverTest
{
    use ConfigurationResolverRegistryTestTrait;

    const RESOLVER_CLASS = DiscreteMultiValueContentResolver::class;
    const MULTI_VALUE_CLASS = DiscreteMultiValue::class;
    const KEYWORD = 'discreteMultiValue';
}
