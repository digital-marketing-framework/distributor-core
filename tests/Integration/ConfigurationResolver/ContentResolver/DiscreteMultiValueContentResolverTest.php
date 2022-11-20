<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration\ConfigurationResolver\ContentResolver;

use DigitalMarketingFramework\Core\Tests\Integration\ConfigurationResolver\ContentResolver\MultiValueContentResolverTest;
use DigitalMarketingFramework\Distributor\Core\ConfigurationResolver\ContentResolver\DiscreteMultiValueContentResolver;
use DigitalMarketingFramework\Distributor\Core\Model\Data\Value\DiscreteMultiValue;
use DigitalMarketingFramework\Distributor\Core\Tests\Integration\ConfigurationResolverRegistryTestTrait;

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
