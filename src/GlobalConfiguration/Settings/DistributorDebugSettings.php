<?php

namespace DigitalMarketingFramework\Distributor\Core\GlobalConfiguration\Settings;

use DigitalMarketingFramework\Core\GlobalConfiguration\Settings\GlobalSettings;
use DigitalMarketingFramework\Distributor\Core\GlobalConfiguration\Schema\DistributorCoreGlobalConfigurationSchema;

class DistributorDebugSettings extends GlobalSettings
{
    public function __construct()
    {
        parent::__construct('distributor-core', DistributorCoreGlobalConfigurationSchema::KEY_DEBUG);
    }

    public function isEnabled(): bool
    {
        return $this->get(DistributorCoreGlobalConfigurationSchema::KEY_DEBUG_ENABLED);
    }

    public function getFile(): string
    {
        return $this->get(DistributorCoreGlobalConfigurationSchema::KEY_DEBUG_FILE);
    }
}
