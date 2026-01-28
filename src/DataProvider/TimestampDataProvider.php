<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\GlobalConfiguration\GlobalConfigurationAwareInterface;
use DigitalMarketingFramework\Core\GlobalConfiguration\GlobalConfigurationAwareTrait;
use DigitalMarketingFramework\Core\GlobalConfiguration\Settings\CoreSettings;
use DigitalMarketingFramework\Core\Model\Data\Value\DateTimeValue;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\StringSchema;

class TimestampDataProvider extends DataProvider implements GlobalConfigurationAwareInterface
{
    use GlobalConfigurationAwareTrait;

    public const KEY_FIELD = 'field';

    public const DEFAULT_FIELD = 'timestamp';

    public const KEY_FORMAT = 'format';

    public const DEFAULT_FORMAT = 'c';

    protected function getDefaultTimezone(): string
    {
        return $this->globalConfiguration->getGlobalSettings(CoreSettings::class)->getDefaultTimezone();
    }

    protected function processContext(WriteableContextInterface $context): void
    {
        $context->copyTimestampFromContext($this->context);
    }

    public function addContext(WriteableContextInterface $context): void
    {
        // NOTE For context management this data provider bypasses the enabled and permission check.
        //      This means it will add context to the submission even if the provider is disabled or permission not granted,
        //      because the timestamp should always be part of the submission context.
        //      We do this to ensure that the submission hash is unique.
        //
        //      This does not mean that the timestamp will actually be added to the form submission data.
        //      Whether that happens still depends on the enabled status and the given permissions.
        $this->processContext($context);
    }

    protected function process(): void
    {
        $timestamp = $this->context->getTimestamp();
        if ($timestamp !== null) {
            $format = $this->getConfig(static::KEY_FORMAT);
            $value = new DateTimeValue((string)$timestamp, $format, $this->getDefaultTimezone());
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
