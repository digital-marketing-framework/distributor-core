<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;

class IpAddressDataProvider extends DataProvider
{
    const KEY_FIELD = 'field';
    const DEFAULT_FIELD = 'ip_address';

    protected function processContext(ContextInterface $context): void
    {
        $this->submission->getContext()->copyIpAddressFromContext($context);
    }

    protected function process(): void
    {
        $value = $this->submission->getContext()->getIpAddress();
        if ($value !== null) {
            $this->setField($this->getConfig(static::KEY_FIELD), $value);
        }
    }

    public static function getDefaultConfiguration(): array
    {
        return parent::getDefaultConfiguration() + [
            static::KEY_FIELD => static::DEFAULT_FIELD,
        ];
    }
}
