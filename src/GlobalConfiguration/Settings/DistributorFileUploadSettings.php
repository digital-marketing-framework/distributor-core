<?php

namespace DigitalMarketingFramework\Distributor\Core\GlobalConfiguration\Settings;

use DigitalMarketingFramework\Core\GlobalConfiguration\Settings\GlobalSettings;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\GlobalConfiguration\Schema\DistributorCoreGlobalConfigurationSchema;

class DistributorFileUploadSettings extends GlobalSettings
{
    public function __construct()
    {
        parent::__construct('distributor-core', DistributorCoreGlobalConfigurationSchema::KEY_FILE_UPLOAD);
    }

    public function disableProcessing(): bool
    {
        return $this->get(DistributorCoreGlobalConfigurationSchema::KEY_FILE_UPLOAD_DISABLE_PROCESSING);
    }

    public function getBaseUploadPath(): string
    {
        return $this->get(DistributorCoreGlobalConfigurationSchema::KEY_FILE_UPLOAD_BASE_PATH);
    }

    /**
     * @return array<string>
     */
    public function getProhibitedExtensions(): array
    {
        return GeneralUtility::castValueToArray(
            strtolower((string)$this->get(DistributorCoreGlobalConfigurationSchema::KEY_FILE_UPLOAD_PROHIBITED_EXTENSION))
        );
    }
}
