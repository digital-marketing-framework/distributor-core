<?php

namespace DigitalMarketingFramework\Distributor\Core;

use DigitalMarketingFramework\Core\ConfigurationResolver\ContentResolver\ContentResolverInterface;
use DigitalMarketingFramework\Core\ConfigurationResolver\Evaluation\EvaluationInterface;
use DigitalMarketingFramework\Core\ConfigurationResolverInitialization as CoreConfigurationResolverInitialization;
use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\ConfigurationResolver\ContentResolver\DiscreteMultiValueContentResolver;
use DigitalMarketingFramework\Distributor\Core\ConfigurationResolver\Evaluation\GateEvaluation;

class ConfigurationResolverInitialization extends Initialization
{
    const PLUGINS = [
        EvaluationInterface::class => [
            GateEvaluation::class,
        ],
        ContentResolverInterface::class => [
            DiscreteMultiValueContentResolver::class,
        ],
    ];
}
