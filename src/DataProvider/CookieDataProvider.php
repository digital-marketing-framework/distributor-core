<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;

class CookieDataProvider extends DataProvider
{
    protected const KEY_COOKIE_FIELD_MAP = 'cookieFieldMap';
    protected const DEFAULT_COOKIE_FIELD_MAP = [];

    protected function processContext(ContextInterface $context): void
    {
        $cookies = array_keys($this->getConfig(static::KEY_COOKIE_FIELD_MAP));
        foreach ($cookies as $cookie) {
            $this->submission->getContext()->copyCookieFromContext($context, $cookie);
        }
    }

    protected function process(): void
    {
        $cookieFieldMap = $this->getConfig(static::KEY_COOKIE_FIELD_MAP);
        foreach ($cookieFieldMap as $cookie => $field) {
            $value = $this->submission->getContext()->getCookie($cookie);
            if ($value !== null) {
                $this->setField($field, $value);
            }
        }
    }

    public static function getDefaultConfiguration(): array
    {
        return parent::getDefaultConfiguration() + [
            static::KEY_COOKIE_FIELD_MAP => static::DEFAULT_COOKIE_FIELD_MAP,
        ];
    }
}
