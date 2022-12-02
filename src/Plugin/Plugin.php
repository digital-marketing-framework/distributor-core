<?php

namespace DigitalMarketingFramework\Distributor\Core\Plugin;

use DigitalMarketingFramework\Core\Plugin\Plugin as CorePlugin;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;

class Plugin extends CorePlugin
{
    public function __construct(
        string $keyword,
        protected RegistryInterface $registry,
    ) {
        parent::__construct($keyword);
    }
}
