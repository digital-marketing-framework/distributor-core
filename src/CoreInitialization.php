<?php

namespace DigitalMarketingFramework\Distributer\Core;

use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryInterface;
use DigitalMarketingFramework\Distributer\Core\DataProvider\CookieDataProvider;
use DigitalMarketingFramework\Distributer\Core\DataProvider\DataProviderInterface;
use DigitalMarketingFramework\Distributer\Core\DataProvider\IpAddressDataProvider;
use DigitalMarketingFramework\Distributer\Core\DataProvider\TimestampDataProvider;

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
