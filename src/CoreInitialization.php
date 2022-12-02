<?php

namespace DigitalMarketingFramework\Distributor\Core;

use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\DataProvider\CookieDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProviderInterface;
use DigitalMarketingFramework\Distributor\Core\DataProvider\IpAddressDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\TimestampDataProvider;

class CoreInitialization extends Initialization
{
    const PLUGINS = [
        DataProviderInterface::class => [
            CookieDataProvider::class,
            IpAddressDataProvider::class,
            TimestampDataProvider::class,
        ],
    ];
    
    public static function initialize(PluginRegistryInterface $registry): void
    {
        ConfigurationResolverInitialization::initialize($registry);
        parent::initialize($registry);
    }
}
