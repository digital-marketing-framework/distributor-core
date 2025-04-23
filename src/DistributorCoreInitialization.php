<?php

namespace DigitalMarketingFramework\Distributor\Core;

use DigitalMarketingFramework\Core\Alert\AlertHandlerInterface;
use DigitalMarketingFramework\Core\DataProcessor\ValueSource\ValueSourceInterface;
use DigitalMarketingFramework\Core\GlobalConfiguration\Schema\GlobalConfigurationSchemaInterface;
use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Core\TestCase\TestCaseProcessorInterface;
use DigitalMarketingFramework\Distributor\Core\Alert\JobWatchAlertHandler;
use DigitalMarketingFramework\Distributor\Core\DataProcessor\ValueSource\DiscreteMultiValueValueSource;
use DigitalMarketingFramework\Distributor\Core\DataProvider\CookieDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataPrivacyDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProviderInterface;
use DigitalMarketingFramework\Distributor\Core\DataProvider\HostDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\IpAddressDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\RefererDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\RequestVariablesDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\TimestampDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\UriDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataSource\ApiEndPointDistributorDataSourceStorage;
use DigitalMarketingFramework\Distributor\Core\GlobalConfiguration\Schema\DistributorCoreGlobalConfigurationSchema;
use DigitalMarketingFramework\Distributor\Core\DataSource\DistributorDataSourceStorageInterface;
use DigitalMarketingFramework\Distributor\Core\TestCase\DistributorTestCaseProcessor;

class DistributorCoreInitialization extends Initialization
{
    protected const PLUGINS = [
        RegistryDomain::CORE => [
            ValueSourceInterface::class => [
                DiscreteMultiValueValueSource::class,
            ],
            AlertHandlerInterface::class => [
                JobWatchAlertHandler::class,
            ],
            TestCaseProcessorInterface::class => [
                DistributorTestCaseProcessor::class,
            ],
        ],
        RegistryDomain::DISTRIBUTOR => [
            DataProviderInterface::class => [
                CookieDataProvider::class,
                DataPrivacyDataProvider::class,
                HostDataProvider::class,
                IpAddressDataProvider::class,
                RefererDataProvider::class,
                RequestVariablesDataProvider::class,
                TimestampDataProvider::class,
                UriDataProvider::class,
            ],
            DistributorDataSourceStorageInterface::class => [
                ApiEndPointDistributorDataSourceStorage::class,
            ],
        ],
    ];

    protected const FRONTEND_SCRIPTS = [
        'distributor' => [
            'dmf-distributor-push.js',
        ],
    ];

    protected const SCHEMA_MIGRATIONS = [];

    public function __construct(string $packageAlias = '', ?GlobalConfigurationSchemaInterface $globalConfigurationSchema = null)
    {
        $globalConfigurationSchema ??= new DistributorCoreGlobalConfigurationSchema();
        parent::__construct('distributor-core', '1.0.0', $packageAlias, $globalConfigurationSchema);
    }
}
