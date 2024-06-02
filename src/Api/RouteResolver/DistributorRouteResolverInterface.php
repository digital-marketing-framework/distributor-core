<?php

namespace DigitalMarketingFramework\Distributor\Core\Api\RouteResolver;

use DigitalMarketingFramework\Core\Api\Route\TemplateRouteInterface;
use DigitalMarketingFramework\Core\Api\RouteResolver\RouteResolverInterface;

interface DistributorRouteResolverInterface extends RouteResolverInterface
{
    public const VARIABLE_DOMAIN = 'domain';

    public const SEGMENT_DISTRIBUTOR = 'distributor';

    public const VARIABLE_END_POINT_SEGMENT = 'end_point';

    public function getEndPointRoute(): TemplateRouteInterface;
}
