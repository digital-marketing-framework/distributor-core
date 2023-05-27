<?php

namespace DigitalMarketingFramework\Distributor\Core\Plugin;

use DigitalMarketingFramework\Core\Plugin\ConfigurablePlugin as CoreConfigurablePlugin;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;

abstract class ConfigurablePlugin extends CoreConfigurablePlugin
{
    public function __construct(
        string $keyword,
        protected RegistryInterface $registry,
    ) {
        parent::__construct($keyword);
    }
}
