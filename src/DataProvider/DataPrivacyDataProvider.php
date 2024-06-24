<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProvider;

use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\DataPrivacy\DataPrivacyManagerAwareInterface;
use DigitalMarketingFramework\Core\DataPrivacy\DataPrivacyManagerAwareTrait;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\Value\BooleanValue;
use DigitalMarketingFramework\Core\SchemaDocument\RenderingDefinition\RenderingDefinitionInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\DataPrivacyPermissionSelectionSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ListSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProvider;

class DataPrivacyDataProvider extends DataProvider implements DataPrivacyManagerAwareInterface
{
    use DataPrivacyManagerAwareTrait;

    public const KEY_FIELDS = 'fields';

    public const KEY_FIELD = 'field';

    public const DEFAULT_FIELD = '';

    public const KEY_CONSENT = 'consent';

    public const KEY_TRUE = 'true';

    public const DEFAULT_TRUE = '1';

    public const KEY_FALSE = 'false';

    public const DEFAULT_FALSE = '0';

    protected function processContext(WriteableContextInterface $context): void
    {
        // data privacy context is already persisted by the data privacy manager
    }

    protected function process(): void
    {
        $fields = $this->getListConfig(static::KEY_FIELDS);
        foreach ($fields as $fieldConfig) {
            $field = $fieldConfig[static::KEY_FIELD];
            $consent = $fieldConfig[static::KEY_CONSENT];
            $true = $fieldConfig[static::KEY_TRUE];
            $false = $fieldConfig[static::KEY_FALSE];

            if ($field !== '') {
                if (!$this->dataPrivacyManager->permissionMatches($consent)) {
                    throw new DigitalMarketingFrameworkException(sprintf('Unknown permission: "%s"', $consent));
                }

                $consentGiven = $this->dataPrivacyManager->getPermission($consent);
                $this->setField($field, new BooleanValue($consentGiven, $true, $false));
            }
        }
    }

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema */
        $schema = parent::getSchema();

        $fieldListItemSchema = new ContainerSchema();
        $fieldListItemSchema->getRenderingDefinition()->setLabel('{consent}');

        $fieldSchema = new StringSchema(static::DEFAULT_FIELD);
        $fieldListItemSchema->addProperty(static::KEY_FIELD, $fieldSchema);

        $consentSchema = new CustomSchema(DataPrivacyPermissionSelectionSchema::TYPE);
        $fieldListItemSchema->addProperty(static::KEY_CONSENT, $consentSchema);

        $trueSchema = new StringSchema(static::DEFAULT_TRUE);
        $trueSchema->getRenderingDefinition()->setLabel('True value');
        $trueSchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $fieldListItemSchema->addProperty(static::KEY_TRUE, $trueSchema);

        $falseSchema = new StringSchema(static::DEFAULT_FALSE);
        $falseSchema->getRenderingDefinition()->setLabel('False value');
        $falseSchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $fieldListItemSchema->addProperty(static::KEY_FALSE, $falseSchema);

        $fieldListSchema = new ListSchema($fieldListItemSchema);
        $fieldListSchema->getRenderingDefinition()->setNavigationItem(false);
        $schema->addProperty(static::KEY_FIELDS, $fieldListSchema);

        return $schema;
    }
}
