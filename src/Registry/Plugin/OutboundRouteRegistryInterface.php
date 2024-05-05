<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRouteInterface;

interface OutboundRouteRegistryInterface extends PluginRegistryInterface
{
    /**
     * @param array<mixed> $additionalArguments
     */
    public function registerOutboundRoute(string $class, array $additionalArguments = [], string $keyword = ''): void;

    /**
     * @return array<OutboundRouteInterface>
     */
    public function getOutboundRoutes(SubmissionDataSetInterface $submission): array;

    public function getOutboundRoute(SubmissionDataSetInterface $submission, string $integrationName, string $routeId): ?OutboundRouteInterface;

    public function deleteOutboundRoute(string $keyword): void;
}
