<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Distributor\Core\Api\DistributorSubmissionHandlerInterface;
use DigitalMarketingFramework\Core\Api\EndPoint\EndPointStorageInterface;
use DigitalMarketingFramework\Distributor\Core\Api\RouteResolver\DistributorRouteResolverInterface;

interface ApiRegistryInterface
{
    public function getApiRouteResolvers(): array;

    public function getDistributorApiRouteResolver(): DistributorRouteResolverInterface;

    public function getDistributorSubmissionHandler(): DistributorSubmissionHandlerInterface;

    public function setDistributorSubmissionHandler(DistributorSubmissionHandlerInterface $distributorApi): void;
}
