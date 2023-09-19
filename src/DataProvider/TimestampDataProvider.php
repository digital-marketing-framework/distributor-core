<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Core\Context\ContextInterface;

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
