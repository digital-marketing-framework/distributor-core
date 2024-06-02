<?php

namespace DigitalMarketingFramework\Distributor\Core\Plugin;

use DigitalMarketingFramework\Core\Integration\IntegrationInfo;
use DigitalMarketingFramework\Core\Model\Configuration\ConfigurationInterface;
use DigitalMarketingFramework\Core\Plugin\IntegrationPlugin as CoreIntegrationPlugin;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;

abstract class IntegrationPlugin extends CoreIntegrationPlugin
{
    public function __construct(
        string $keyword,
        IntegrationInfo $integrationInfo,
        ConfigurationInterface $configuration,
        protected RegistryInterface $registry,
    ) {
        parent::__construct($keyword, $integrationInfo, $configuration);
    }
}
