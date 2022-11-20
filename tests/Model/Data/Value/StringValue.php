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
    public $value;

    public function __construct(string $value = '')
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function pack(): array
    {
        return [(string)$this->value];
    }

    public static function unpack(array $packed): ValueInterface
    {
        $field = new StringValue();
        $field->value = $packed[0];
        return $field;
    }
}
