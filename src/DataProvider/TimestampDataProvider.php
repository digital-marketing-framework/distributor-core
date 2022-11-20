<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;

class TimestampDataProvider extends DataProvider
{
    const KEY_FIELD = 'field';
    const DEFAULT_FIELD = 'timestamp';

    const KEY_FORMAT = 'format';
    const DEFAULT_FORMAT = 'c';

    protected function processContext(ContextInterface $context): void
    {
        $this->submission->getContext()->copyTimestampFromContext($context);
    }

    protected function process(): void
    {
        $timestamp = $this->submission->getContext()->getTimestamp();
        if ($timestamp !== null) {
            $format = $this->getConfig(static::KEY_FORMAT);
            $value = date($format, $timestamp);
            $this->setField($this->getConfig(static::KEY_FIELD), $value);
        }
    }

    public static function getDefaultConfiguration(): array
    {
        return parent::getDefaultConfiguration() + [
            static::KEY_FIELD => static::DEFAULT_FIELD,
            static::KEY_FORMAT => static::DEFAULT_FORMAT,
        ];
    }
}
