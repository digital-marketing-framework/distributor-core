<?php

namespace DigitalMarketingFramework\Distributer\Core\DataProvider;

use DigitalMarketingFramework\Core\Request\RequestInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;

class TimestampDataProvider extends DataProvider
{
    const KEY_FIELD = 'field';
    const DEFAULT_FIELD = 'timestamp';

    const KEY_FORMAT = 'format';
    const DEFAULT_FORMAT = 'c';

    protected function processContext(SubmissionDataSetInterface $submission, RequestInterface $request): void
    {
        $format = $this->getConfig(static::KEY_FORMAT);
        $this->addToContext($submission, 'timestamp', date($format));
    }

    protected function process(SubmissionDataSetInterface $submission): void
    {
        $this->setFieldFromContext(
            $submission,
            'timestamp',
            $this->getConfig(static::KEY_FIELD)
        );
    }

    public static function getDefaultConfiguration(): array
    {
        return parent::getDefaultConfiguration() + [
            static::KEY_FIELD => static::DEFAULT_FIELD,
            static::KEY_FORMAT => static::DEFAULT_FORMAT,
        ];
    }
}
