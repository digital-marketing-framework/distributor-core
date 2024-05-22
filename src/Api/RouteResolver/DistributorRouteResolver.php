<?php

namespace DigitalMarketingFramework\Distributor\Core\Api\RouteResolver;

use DigitalMarketingFramework\Core\Api\Response\ApiResponse;
use DigitalMarketingFramework\Core\Api\Route\TemplateRoute;
use DigitalMarketingFramework\Core\Api\Route\TemplateRouteInterface;
use DigitalMarketingFramework\Core\Api\RouteResolver\EntryRouteResolverInterface;
use DigitalMarketingFramework\Distributor\Core\Api\DistributorSubmissionHandlerInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Core\Api\Request\ApiRequestInterface;
use DigitalMarketingFramework\Core\Api\Response\ApiResponseInterface;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;

class DistributorRouteResolver implements DistributorRouteResolverInterface
{
    public const VARIABLE_DOMAIN = 'domain';

    public const SEGMENT_DISTRIBUTOR = 'distributor';

    public const VARIABLE_END_POINT_SEGMENT = 'end_point';

    protected DistributorSubmissionHandlerInterface $distributorSubmissionHandler;

    public function __construct(
        protected RegistryInterface $registry,
    ) {
        $this->distributorSubmissionHandler = $this->registry->getDistributorSubmissionHandler();
    }

    public function resolveRequest(ApiRequestInterface $request): ApiResponseInterface
    {
        $endpointSegment = GeneralUtility::dashedToCamelCase($request->getVariable(static::VARIABLE_END_POINT_SEGMENT));
        $data = $request->getPayload();
        $context = $request->getContext();
        $this->distributorSubmissionHandler->submitToEndPointByName($endpointSegment, $data, $context);

        return new ApiResponse(['success' => true], 200);
    }

    public function getEndPointRoute(): TemplateRouteInterface
    {
        return new TemplateRoute(
            id: static::SEGMENT_DISTRIBUTOR,
            template: implode('/', [
                GeneralUtility::slugify(static::SEGMENT_DISTRIBUTOR),
                '{' . static::VARIABLE_END_POINT . '}',
            ]),
            variables: [
                static::VARIABLE_END_POINT => '',
            ],
            constants: [
                static::VARIABLE_DOMAIN => static::SEGMENT_DISTRIBUTOR,
            ],
            methods: ['POST']
        );
    }

    public function getAllRoutes(): array
    {
        return [$this->getEndPointRoute()];
    }

    public function getAllResourceRoutes(): array
    {
        $routes = [];
        $endPointRoute = $this->getEndPointRoute();
        foreach ($this->distributorSubmissionHandler->getEndPointNames() as $segment) {
            $route = $endPointRoute->getResourceRoute(
                idAffix: $segment,
                variables: [
                    static::VARIABLE_END_POINT_SEGMENT => GeneralUtility::slugify($segment),
                ]);
            $routes[$route->getId()] = $route;
        }
        return $routes;
    }
}
