<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Distributor\Core\Api\DistributorSubmissionHandlerInterface;
use DigitalMarketingFramework\Distributor\Core\Api\EndPoint\EndPointStorageInterface;
use DigitalMarketingFramework\Distributor\Core\Api\RouteResolver\DistributorRouteResolverInterface;

interface ApiRegistryInterface
{
    public function getEndPointStorage(): EndPointStorageInterface;

    public function setEndPointStorage(EndPointStorageInterface $endPointStorage): void;

    public function getApiRouteResolvers(): array;

    public function getDistributorApiRouteResolver(): DistributorRouteResolverInterface;

    public function getDistributorSubmissionHandler(): DistributorSubmissionHandlerInterface;

    public function setDistributorSubmissionHandler(DistributorSubmissionHandlerInterface $distributorApi): void;
}
