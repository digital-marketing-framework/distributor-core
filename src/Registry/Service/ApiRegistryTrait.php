<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Core\Utility\GeneralUtility;
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

    public function getFrontendSettings(): array
    {
        $settings = parent::getFrontendSettings();
        $endPointStorage = $this->getEndPointStorage();
        $entryRouteResolver = $this->getApiEntryRouteResolver();
        $distributorRouteResolver = $this->getDistributorApiRouteResolver();

        $endPointRoute = $distributorRouteResolver->getEndPointRoute();
        $endPoints = $endPointStorage->getAllEndPoints();
        foreach ($endPoints as $endPoint) {
            $route = $endPointRoute->getResourceRoute(
                idAffix: $endPoint->getName(),
                variables: [
                    DistributorRouteResolverInterface::VARIABLE_END_POINT_SEGMENT => GeneralUtility::slugify($endPoint->getName()),
                ]
            );
            $id = $route->getId();
            $settings['pluginSettings'][$id] = []; // TODO do we need plugin settings for distributor end points?
            $settings['urls'][$id] = $entryRouteResolver->getFullPath($route->getPath());
        }

        return $settings;
    }
}
