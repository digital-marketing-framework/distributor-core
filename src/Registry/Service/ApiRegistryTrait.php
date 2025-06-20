<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Service;

use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\Api\DistributorSubmissionHandler;
use DigitalMarketingFramework\Distributor\Core\Api\DistributorSubmissionHandlerInterface;
use DigitalMarketingFramework\Distributor\Core\Api\RouteResolver\DistributorRouteResolver;
use DigitalMarketingFramework\Distributor\Core\Api\RouteResolver\DistributorRouteResolverInterface;

trait ApiRegistryTrait
{
    protected DistributorRouteResolverInterface $distributorRouteResolver;

    protected DistributorSubmissionHandlerInterface $distributorSubmissionHandler;

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
        $entryRouteResolver = $this->getRegistryCollection()->getApiEntryRouteResolver();
        $distributorRouteResolver = $this->getDistributorApiRouteResolver();

        $endPointRoute = $distributorRouteResolver->getEndPointRoute();
        $endPoints = $endPointStorage->fetchAll();
        foreach ($endPoints as $endPoint) {
            if (!$endPoint->getEnabled()) {
                continue;
            }

            if (!$endPoint->getExposeToFrontend()) {
                continue;
            }

            $route = $endPointRoute->getResourceRoute(
                idAffix: $endPoint->getName(),
                variables: [
                    DistributorRouteResolverInterface::VARIABLE_END_POINT_SEGMENT => GeneralUtility::slugify($endPoint->getName()),
                ]
            );

            $id = $route->getId();
            $settings['pluginSettings'][$id] = [
                'contextDisabled' => $endPoint->getDisableContext(),
                'allowContextOverride' => $endPoint->getAllowContextOverride(),
            ];
            $settings['urls'][$id] = $entryRouteResolver->getFullPath($route->getPath());
        }

        return $settings;
    }
}
