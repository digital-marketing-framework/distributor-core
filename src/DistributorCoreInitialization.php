<?php

namespace DigitalMarketingFramework\Distributor\Core;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\RenderingDefinition\RenderingDefinitionInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\IntegerSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Core\DataProcessor\Evaluation\EvaluationInterface;
use DigitalMarketingFramework\Core\DataProcessor\ValueSource\ValueSourceInterface;
use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Distributor\Core\DataProcessor\Evaluation\GateEvaluation;
use DigitalMarketingFramework\Distributor\Core\DataProcessor\ValueSource\DiscreteMultiValueValueSource;
use DigitalMarketingFramework\Distributor\Core\DataProvider\CookieDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProviderInterface;
use DigitalMarketingFramework\Distributor\Core\DataProvider\IpAddressDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\RequestVariablesDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\TimestampDataProvider;

class DistributorCoreInitialization extends Initialization
{
    protected const PLUGINS = [
        RegistryDomain::CORE => [
            EvaluationInterface::class => [
                GateEvaluation::class,
            ],
            ValueSourceInterface::class => [
                DiscreteMultiValueValueSource::class,
            ],
        ],
        RegistryDomain::DISTRIBUTOR => [
            DataProviderInterface::class => [
                CookieDataProvider::class,
                IpAddressDataProvider::class,
                RequestVariablesDataProvider::class,
                TimestampDataProvider::class,
            ],
        ],
    ];

    protected const SCHEMA_MIGRATIONS = [];

    protected function getGlobalConfigurationSchema(): ?SchemaInterface
    {
        $schema = new ContainerSchema();
        $schema->getRenderingDefinition()->setLabel('Distributor Core');

        // queue
        $queueSchema = new ContainerSchema();
        $queueSchema->getRenderingDefinition()->setNavigationItem(false);

        $queueSchema->addProperty('pid', new IntegerSchema(0));

        $expirationTimeSchema = new IntegerSchema(30);
        $expirationTimeSchema->getRenderingDefinition()->setLabel('Expiration Time (days)');
        $queueSchema->addProperty('expirationTime', $expirationTimeSchema);

        $schema->addProperty('queue', $queueSchema);

        // field upload
        $fileUploadSchema = new ContainerSchema();
        $fileUploadSchema->getRenderingDefinition()->setNavigationItem(false);

        $fileUploadSchema->addProperty('disableProcessing', new BooleanSchema(false));

        $fileUploadSchema->addProperty('baseUploadPath', new StringSchema('uploads/digital_marketing_framework/form_uploads/'));

        $fileUploadSchema->addProperty('prohibitedExtension', new StringSchema('php,exe'));

        $schema->addProperty('fileUpload', $fileUploadSchema);

        // debug
        $debugSchema = new ContainerSchema();
        $debugSchema->getRenderingDefinition()->setNavigationItem(false);

        $debugSchema->addProperty('enabled', new BooleanSchema(false));

        $debugSchema->addProperty('file', new StringSchema('digital-marketing-framework-distributor-submission.log'));

        $schema->addProperty('debug', $debugSchema);

        return $schema;
    }

    public function __construct(string $packageAlias = '')
    {
        parent::__construct('distributor-core', '1.0.0', $packageAlias);
    }
}
