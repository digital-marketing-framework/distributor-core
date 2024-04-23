<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\MapSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Core\Context\ContextInterface;

class CookieDataProvider extends DataProvider
{
    public const KEY_COOKIE_FIELD_MAP = 'cookieFieldMap';

    public const DEFAULT_COOKIE_FIELD_MAP = [];

    protected function processContext(ContextInterface $context): void
    {
        $cookies = array_keys($this->getMapConfig(static::KEY_COOKIE_FIELD_MAP));
        foreach ($cookies as $cookie) {
            $this->submission->getContext()->copyCookieFromContext($context, $cookie);
        }
    }

    protected function process(): void
    {
        $cookieFieldMap = $this->getMapConfig(static::KEY_COOKIE_FIELD_MAP);
        foreach ($cookieFieldMap as $cookie => $field) {
            $value = $this->submission->getContext()->getCookie($cookie);
            if ($value !== null) {
                $this->setField($field, $value);
            }
        }
    }

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema $schema */
        $schema = parent::getSchema();
        $cookieMapSchema = new MapSchema(new StringSchema());
        $cookieMapSchema->getRenderingDefinition()->setNavigationItem(false);
        $schema->addProperty(static::KEY_COOKIE_FIELD_MAP, $cookieMapSchema);

        return $schema;
    }
}
