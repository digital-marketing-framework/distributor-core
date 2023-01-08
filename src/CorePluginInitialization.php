<?php

namespace DigitalMarketingFramework\Distributor\Core;

use DigitalMarketingFramework\Core\ConfigurationResolver\ContentResolver\ContentResolverInterface;
use DigitalMarketingFramework\Core\ConfigurationResolver\Evaluation\EvaluationInterface;
use DigitalMarketingFramework\Core\PluginInitialization;
use DigitalMarketingFramework\Distributor\Core\ConfigurationResolver\ContentResolver\DiscreteMultiValueContentResolver;
use DigitalMarketingFramework\Distributor\Core\ConfigurationResolver\Evaluation\GateEvaluation;

class CorePluginInitialization extends PluginInitialization
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
