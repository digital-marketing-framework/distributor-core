<?php

namespace DigitalMarketingFramework\Distributor\Core\GlobalConfiguration\Schema;

use DigitalMarketingFramework\Core\GlobalConfiguration\Schema\GlobalConfigurationSchema;
use DigitalMarketingFramework\Core\Queue\GlobalConfiguration\Schema\QueueSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\StringSchema;

class DistributorCoreGlobalConfigurationSchema extends GlobalConfigurationSchema
{
    public const KEY_FILE_UPLOAD = 'fileUpload';

    public const KEY_FILE_UPLOAD_DISABLE_PROCESSING = 'disableProcessing';

    public const DEFAULT_FILE_UPLOAD_DISABLE_PROCESSING = false;

    public const KEY_FILE_UPLOAD_BASE_PATH = 'baseUploadPath';

    public const DEFAULT_FILE_UPLOAD_BASE_PATH = 'uploads/digital_marketing_framework/form_uploads/';

    public const KEY_FILE_UPLOAD_PROHIBITED_EXTENSION = 'prohibitedExtension';

    public const DEFAULT_FILE_UPLOAD_PROHIBITED_EXTENSION = 'php,exe';

    public const KEY_DEBUG = 'debug';

    public const KEY_DEBUG_ENABLED = 'enabled';

    public const DEFAULT_DEBUG_ENABLED = false;

    public const KEY_DEBUG_FILE = 'file';

    public const DEFAULT_DEBUG_FILE = 'ditigal-marketing-framework-distributor-submission.log';

    protected ContainerSchema $fileUploadSchema;

    protected ContainerSchema $debugSchema;

    public function __construct(protected ?QueueSchema $queueSchema = new QueueSchema())
    {
        parent::__construct();
        $this->getRenderingDefinition()->setLabel('Distributor');
        $this->addProperty(QueueSchema::KEY_QUEUE, $this->queueSchema);

        $this->fileUploadSchema = $this->getFileUploadSchema();
        $this->addProperty(static::KEY_FILE_UPLOAD, $this->fileUploadSchema);

        $this->debugSchema = $this->getDebugSchema();
        $this->addProperty(static::KEY_DEBUG, $this->debugSchema);
    }

    public function getWeight(): int
    {
        return 60;
    }

    protected function getFileUploadSchema(): ContainerSchema
    {
        $schema = new ContainerSchema();

        $disableProcessingSchema = new BooleanSchema(static::DEFAULT_FILE_UPLOAD_DISABLE_PROCESSING);
        $schema->addProperty(static::KEY_FILE_UPLOAD_DISABLE_PROCESSING, $disableProcessingSchema);

        $basePathSchema = new StringSchema(static::DEFAULT_FILE_UPLOAD_BASE_PATH);
        $schema->addProperty(static::KEY_FILE_UPLOAD_BASE_PATH, $basePathSchema);

        $prohibitedExtensionSchema = new StringSchema(static::DEFAULT_FILE_UPLOAD_PROHIBITED_EXTENSION);
        $schema->addProperty(static::KEY_FILE_UPLOAD_PROHIBITED_EXTENSION, $prohibitedExtensionSchema);

        return $schema;
    }

    protected function getDebugSchema(): ContainerSchema
    {
        $schema = new ContainerSchema();

        $enabledSchema = new BooleanSchema(static::DEFAULT_DEBUG_ENABLED);
        $schema->addProperty(static::KEY_DEBUG_ENABLED, $enabledSchema);

        $fileSchema = new StringSchema(static::DEFAULT_DEBUG_FILE);
        $schema->addProperty(static::KEY_DEBUG_FILE, $fileSchema);

        return $schema;
    }
}
