<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\StringSchema;

class IpAddressDataProvider extends DataProvider
{
    public const KEY_FIELD = 'field';

    public const DEFAULT_FIELD = 'ip_address';

    protected function processContext(WriteableContextInterface $context): void
    {
        $context->copyIpAddressFromContext($this->context);
    }

    protected function process(): void
    {
        $value = $this->context->getIpAddress();
        if ($value !== null) {
            $this->setField($this->getConfig(static::KEY_FIELD), $value);
        }
    }

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema $schema */
        $schema = parent::getSchema();
        $schema->addProperty(static::KEY_FIELD, new StringSchema(static::DEFAULT_FIELD));

        return $schema;
    }

    public static function getLabel(): ?string
    {
        return 'IP Address';
    }
}
