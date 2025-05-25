<?php

namespace DigitalMarketingFramework\Distributor\Core;

use DigitalMarketingFramework\Core\Alert\AlertHandlerInterface;
use DigitalMarketingFramework\Core\Backend\Controller\SectionController\SectionControllerInterface;
use DigitalMarketingFramework\Core\Backend\Section\Section;
use DigitalMarketingFramework\Core\DataProcessor\ValueSource\ValueSourceInterface;
use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Distributor\Core\Alert\JobWatchAlertHandler;
use DigitalMarketingFramework\Distributor\Core\Backend\Controller\SectionController\DistributorErrorMonitorSectionController;
use DigitalMarketingFramework\Distributor\Core\Backend\Controller\SectionController\DistributorListSectionController;
use DigitalMarketingFramework\Distributor\Core\Backend\Controller\SectionController\DistributorStatisticsSectionController;
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
use DigitalMarketingFramework\Distributor\Core\GlobalConfiguration\Schema\DistributorCoreGlobalConfigurationSchema;

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
            SectionControllerInterface::class => [
                DistributorStatisticsSectionController::class,
                DistributorListSectionController::class,
                DistributorErrorMonitorSectionController::class,
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
        ],
    ];

    protected const FRONTEND_SCRIPTS = [
        'distributor' => [
            'dmf-distributor-push.js',
        ],
    ];

    protected const SCHEMA_MIGRATIONS = [];

    protected function getBackendSections(): array
    {
        return [
            new Section(
                'Distributor',
                'DISTRIBUTOR',
                'page.distributor.show-statistics',
                'Distributor Job Management',
                'PKG:digital-marketing-framework/distributor-core/res/assets/icons/dashboard-distributor.svg',
                'Show',
                50
            ),
        ];
    }

    public function __construct(string $packageAlias = '')
    {
        parent::__construct('distributor-core', '1.0.0', $packageAlias, new DistributorCoreGlobalConfigurationSchema());
    }
}
