<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Core\Api\RouteResolver\RouteResolverInterface;
use DigitalMarketingFramework\Distributor\Core\Api\DistributorSubmissionHandlerInterface;
use DigitalMarketingFramework\Distributor\Core\Api\RouteResolver\DistributorRouteResolverInterface;

interface ApiRegistryInterface
{
    /**
     * @return array<string,RouteResolverInterface>
     */
    public function getApiRouteResolvers(): array;

    public function getDistributorApiRouteResolver(): DistributorRouteResolverInterface;

    public function getDistributorSubmissionHandler(): DistributorSubmissionHandlerInterface;

    public function setDistributorSubmissionHandler(DistributorSubmissionHandlerInterface $distributorApi): void;
}
