<?php

namespace DigitalMarketingFramework\Distributer\Core;

use DigitalMarketingFramework\Core\ConfigurationResolver\ContentResolver\ContentResolverInterface;
use DigitalMarketingFramework\Core\ConfigurationResolver\Evaluation\EvaluationInterface;
use DigitalMarketingFramework\Core\ConfigurationResolverInitialization as CoreConfigurationResolverInitialization;
use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryInterface;
use DigitalMarketingFramework\Distributer\Core\ConfigurationResolver\ContentResolver\DiscreteMultiValueContentResolver;
use DigitalMarketingFramework\Distributer\Core\ConfigurationResolver\Evaluation\GateEvaluation;

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
    
    public static function initialize(PluginRegistryInterface $registry): void
    {
        CoreConfigurationResolverInitialization::initialize($registry);
        parent::initialize($registry);
    }
}
