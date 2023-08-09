<?php

namespace DigitalMarketingFramework\Distributor\Core;

use DigitalMarketingFramework\Core\DataProcessor\Evaluation\EvaluationInterface;
use DigitalMarketingFramework\Core\DataProcessor\ValueSource\ValueSourceInterface;
use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Distributor\Core\DataProcessor\Evaluation\GateEvaluation;
use DigitalMarketingFramework\Distributor\Core\DataProcessor\ValueSource\DiscreteMultiValueValueSource;
use DigitalMarketingFramework\Distributor\Core\DataProvider\CookieDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProviderInterface;
use DigitalMarketingFramework\Distributor\Core\DataProvider\IpAddressDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\RequestVariablesDataProvider;
use DigitalMarketingFramework\Distributor\Core\DataProvider\TimestampDataProvider;

class DistributorCoreInitialization extends Initialization
{
    protected const PLUGINS = [
        RegistryDomain::CORE => [
            EvaluationInterface::class => [
                GateEvaluation::class,
            ],
            ValueSourceInterface::class => [
                DiscreteMultiValueValueSource::class,
            ],
        ],
        RegistryDomain::DISTRIBUTOR => [
            DataProviderInterface::class => [
                CookieDataProvider::class,
                IpAddressDataProvider::class,
                RequestVariablesDataProvider::class,
                TimestampDataProvider::class,
            ],
        ],
    ];

    protected const SCHEMA_MIGRATIONS = [];

    public function __construct()
    {
        parent::__construct('distributor-core', '1.0.0');
    }
}
