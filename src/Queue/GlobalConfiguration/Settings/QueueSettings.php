<?php

namespace DigitalMarketingFramework\Distributor\Core\Queue\GlobalConfiguration\Settings;

use DigitalMarketingFramework\Core\Queue\GlobalConfiguration\Settings\QueueSettings as CoreQueueSettings;

class QueueSettings extends CoreQueueSettings
{
    public function __construct()
    {
        parent::__construct('distributor-core');
    }
}
