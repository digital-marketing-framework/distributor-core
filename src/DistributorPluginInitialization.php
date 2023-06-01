<?php

namespace DigitalMarketingFramework\Distributor\Core;

use DigitalMarketingFramework\Core\PluginInitialization;
use DigitalMarketingFramework\Distributor\Core\DataProvider\CookieDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProviderInterface;
use DigitalMarketingFramework\Distributor\Core\DataProvider\IpAddressDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\RequestVariablesDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\TimestampDataProvider;

class DistributorPluginInitialization extends PluginInitialization
{
    const PLUGINS = [
        DataProviderInterface::class => [
            CookieDataProvider::class,
            IpAddressDataProvider::class,
            RequestVariablesDataProvider::class,
            TimestampDataProvider::class,
        ],
    ];
}
