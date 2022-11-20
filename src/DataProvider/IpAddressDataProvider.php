<?php

namespace DigitalMarketingFramework\Distributer\Core\DataProvider;

use DigitalMarketingFramework\Core\Request\RequestInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;

class IpAddressDataProvider extends DataProvider
{
    const KEY_FIELD = 'field';
    const DEFAULT_FIELD = 'ip_address';

    protected function processContext(SubmissionDataSetInterface $submission, RequestInterface $request): void
    {
        $this->addToContext($submission, 'ip_address', $request->getIpAddress());
    }

    protected function process(SubmissionDataSetInterface $submission): void
    {
        $this->setFieldFromContext(
            $submission,
            'ip_address',
            $this->getConfig(static::KEY_FIELD)
        );
    }

    public static function getDefaultConfiguration(): array
    {
        return parent::getDefaultConfiguration() + [
            static::KEY_FIELD => static::DEFAULT_FIELD,
        ];
    }
}
