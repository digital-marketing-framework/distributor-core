<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Model\Data\Value;

use DigitalMarketingFramework\Core\Model\Data\Value\Value;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;

/**
 * This dummy class has to exist because a mock can't have static methods
 * and the static method "unpack" is called by the QueryDataFactory
 */
class StringValue extends Value
{
    public function __construct(public string $value = '')
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function pack(): array
    {
        return ['value' => $this->value];
    }

    public static function unpack(array $packed): ValueInterface
    {
        return new StringValue($packed['value']);
    }
}
