<?php

namespace DigitalMarketingFramework\Distributer\Core\DataProvider;

use DigitalMarketingFramework\Core\Request\RequestInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;

class CookieDataProvider extends DataProvider
{
    const KEY_COOKIE_FIELD_MAP = 'cookieFieldMap';
    const DEFAULT_COOKIE_FIELD_MAP = [];

    protected function processContext(SubmissionDataSetInterface $submission, RequestInterface $request): void
    {
        $cookies = array_keys($this->getConfig(static::KEY_COOKIE_FIELD_MAP));
        foreach ($cookies as $cookie) {
            $this->addCookieToContext($submission, $request, $cookie);
        }
    }

    protected function process(SubmissionDataSetInterface $submission): void
    {
        $cookieFieldMap = $this->getConfig(static::KEY_COOKIE_FIELD_MAP);
        foreach ($cookieFieldMap as $cookie => $field) {
            $this->setFieldFromCookie($submission, $cookie, $field);
        }
    }

    public static function getDefaultConfiguration(): array
    {
        return parent::getDefaultConfiguration() + [
            static::KEY_COOKIE_FIELD_MAP => static::DEFAULT_COOKIE_FIELD_MAP,
        ];
    }
}
