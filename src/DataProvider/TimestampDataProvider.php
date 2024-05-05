<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\StringSchema;

class TimestampDataProvider extends DataProvider
{
    public const KEY_FIELD = 'field';

    public const DEFAULT_FIELD = 'timestamp';

    public const KEY_FORMAT = 'format';

    public const DEFAULT_FORMAT = 'c';

    protected function processContext(ContextInterface $context): void
    {
        $this->submission->getContext()->copyTimestampFromContext($context);
    }

    public function addContext(ContextInterface $context): void
    {
        // NOTE For context management this data provider bypasses the enabled check.
        //      This means it will add context to the submission even if the provider is disabled,
        //      because the timestamp should always be part of the submission context.
        //      We do this to ensure that the submission hash is unique.
        //
        //      This does not mean that the timestamp will actually be added to the form submission data.
        //      Whether or not that happens still depends on the enabled status.
        $this->processContext($context);
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

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema $schema */
        $schema = parent::getSchema();
        $schema->addProperty(static::KEY_FIELD, new StringSchema(static::DEFAULT_FIELD));
        $schema->addProperty(static::KEY_FORMAT, new StringSchema(static::DEFAULT_FORMAT));

        return $schema;
    }
}
