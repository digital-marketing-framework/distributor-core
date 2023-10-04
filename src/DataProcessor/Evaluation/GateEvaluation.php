<?php

namespace DigitalMarketingFramework\Distributor\Core\DataProcessor\Evaluation;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\DataProcessor\Evaluation\Evaluation;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Configuration\ConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\ConfigurationDocument\SchemaDocument\Schema\Custom\RouteReferenceSchema;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfiguration;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;

class GateEvaluation extends Evaluation
{
    public const KEY_ROUTE_ID = 'routeId';

    public const KEY_ROUTE_IDS_EVALUATED = 'gateRouteIdsEvaluated';

    public const MESSAGE_LOOP_DETECTED = 'Gate dependency loop found for ID %s!';

    public const MESSAGE_ROUTE_NOT_FOUND = 'Route with ID %s not found!';

    /**
     * @return ?array<string,mixed>
     */
    protected function getRouteConfiguration(string $routeId): ?array
    {
        $configuration = $this->context['configuration'] ?? null;
        if ($configuration instanceof ConfigurationInterface) {
            $configuration = SubmissionConfiguration::convert($configuration);

            return $configuration->getRouteConfiguration($routeId);
        }

        return null;
    }

    protected function loopDetection(string $routeId): void
    {
        if (!isset($this->context[static::KEY_ROUTE_IDS_EVALUATED])) {
            $this->context[static::KEY_ROUTE_IDS_EVALUATED] = [];
        }

        if (isset($this->context[static::KEY_ROUTE_IDS_EVALUATED][$routeId])) {
            throw new DigitalMarketingFrameworkException(sprintf(static::MESSAGE_LOOP_DETECTED, $routeId));
        }
    }

    public function evaluate(): bool
    {
        $routeId = $this->getConfig(static::KEY_ROUTE_ID);
        $this->loopDetection($routeId);
        $this->context[static::KEY_ROUTE_IDS_EVALUATED][$routeId] = true;

        $routeConfiguration = $this->getRouteConfiguration($routeId);
        if ($routeConfiguration === null) {
            throw new DigitalMarketingFrameworkException(sprintf(static::MESSAGE_ROUTE_NOT_FOUND, $routeId));
        }

        $enabled = $routeConfiguration[RouteInterface::KEY_ENABLED] ?? RouteInterface::DEFAULT_ENABLED;
        $gate = $routeConfiguration[RouteInterface::KEY_GATE] ?? RouteInterface::DEFAULT_GATE;

        if (!$enabled) {
            $result = false;
        } elseif (empty($gate)) {
            $result = true;
        } else {
            $result = $this->dataProcessor->processEvaluation($gate, $this->context->copy());
        }

        unset($this->context[static::KEY_ROUTE_IDS_EVALUATED][$routeId]);

        return $result;
    }

    public static function getSchema(): SchemaInterface
    {
        $schema = new ContainerSchema();
        $schema->addProperty(static::KEY_ROUTE_ID, new RouteReferenceSchema());

        return $schema;
    }
}
