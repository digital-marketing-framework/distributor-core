<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Distributor\Core\Api\DistributorSubmissionHandler;
use DigitalMarketingFramework\Distributor\Core\Api\DistributorSubmissionHandlerInterface;
use DigitalMarketingFramework\Distributor\Core\Api\RouteResolver\DistributorRouteResolver;
use DigitalMarketingFramework\Distributor\Core\Api\EndPoint\EndPointStorage;
use DigitalMarketingFramework\Distributor\Core\Api\EndPoint\EndPointStorageInterface;
use DigitalMarketingFramework\Distributor\Core\Api\RouteResolver\DistributorRouteResolverInterface;

trait ApiRegistryTrait
{
    protected EndPointStorageInterface $endPointStorage;

    protected DistributorRouteResolverInterface $distributorRouteResolver;

    protected DistributorSubmissionHandlerInterface $distributorSubmissionHandler;

    public function getEndPointStorage(): EndPointStorageInterface
    {
        if (!isset($this->endPointStorage)) {
            $this->endPointStorage = $this->createObject(EndPointStorage::class);
        }

        return $this->endPointStorage;
    }

    public function setEndPointStorage(EndPointStorageInterface $endPointStorage): void
    {
        $this->endPointStorage = $endPointStorage;
    }

    public function getDistributorApiRouteResolver(): DistributorRouteResolverInterface
    {
        if (!isset($this->distributorRouteResolver)) {
            $this->distributorRouteResolver = $this->createObject(DistributorRouteResolver::class, [$this]);
        }

        return $this->distributorRouteResolver;
    }

    public function getApiRouteResolvers(): array
    {
        return [
            'distributor' => $this->getDistributorApiRouteResolver(),
        ];
    }

    public function getDistributorSubmissionHandler(): DistributorSubmissionHandlerInterface
    {
        if (!isset($this->distributorSubmissionHandler)) {
            $this->distributorSubmissionHandler = $this->createObject(DistributorSubmissionHandler::class, [$this]);
        }

        return $this->distributorSubmissionHandler;
    }

    public function setDistributorSubmissionHandler(DistributorSubmissionHandlerInterface $distributorApi): void
    {
        $this->distributorSubmissionHandler = $distributorApi;
    }
}
