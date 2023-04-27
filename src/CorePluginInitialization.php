<?php

namespace DigitalMarketingFramework\Distributor\Core;

use DigitalMarketingFramework\Core\DataProcessor\Evaluation\EvaluationInterface;
use DigitalMarketingFramework\Core\DataProcessor\ValueSource\ValueSourceInterface;
use DigitalMarketingFramework\Core\PluginInitialization;
use DigitalMarketingFramework\Distributor\Core\DataProcessor\Evaluation\GateEvaluation;
use DigitalMarketingFramework\Distributor\Core\DataProcessor\ValueSource\DiscreteMultiValueValueSource;

class CorePluginInitialization extends PluginInitialization
{
    const PLUGINS = [
        EvaluationInterface::class => [
            GateEvaluation::class,
        ],
        ValueSourceInterface::class => [
            DiscreteMultiValueValueSource::class,
        ],
    ];
}
