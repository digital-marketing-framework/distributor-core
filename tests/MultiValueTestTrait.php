<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests;

use DigitalMarketingFramework\Distributer\Core\Model\Form\MultiValueField;

trait MultiValueTestTrait // extends \PHPUnit\Framework\TestCase
{
    public static function assertMultiValue($actual, string $class = MultiValueField::class)
    {
        static::assertIsObject($actual);
        if ($class !== MultiValueField::class) {
            static::assertInstanceOf(MultiValueField::class, $actual);
        }
        static::assertEquals($class, get_class($actual));
    }

    public static function assertMultiValueEquals($expected, $actual, string $class = MultiValueField::class)
    {
        /** @var MultiValueField $actual */
        static::assertMultiValue($actual, $class);

        if ($expected instanceof MultiValueField) {
            $expected = $expected->toArray();
        }
        $actual = $actual->toArray();
        static::assertEquals(array_keys($actual), array_keys($expected));

        foreach ($expected as $key => $value) {
            if (is_scalar($value)) {
                static::assertEquals($actual[$key], $value);
            } elseif ($value instanceof MultiValueField) {
                static::assertMultiValueEquals($value, $actual[$key], get_class($value));
            } else {
                static::assertMultiValueEquals($value, $actual[$key]);
            }
        }
    }

    public static function assertMultiValueEmpty($actual, string $class = MultiValueField::class)
    {
        static::assertMultiValue($actual, $class);
        /** @var MultiValueField $actual */
        static::assertEmpty($actual->toArray());
    }
}
