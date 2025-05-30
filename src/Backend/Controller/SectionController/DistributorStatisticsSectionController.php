<?php

namespace DigitalMarketingFramework\Distributor\Core\Backend\Controller\SectionController;

use DigitalMarketingFramework\Core\Backend\Response\Response;
use DigitalMarketingFramework\Core\Registry\RegistryInterface;

class DistributorStatisticsSectionController extends DistributorSectionController
{
    public function __construct(
        string $keyword,
        RegistryInterface $registry,
    ) {
        parent::__construct($keyword, $registry, ['show-statistics']);
    }

    protected function showStatisticsAction(): Response
    {
        $this->addListScript();

        $filters = $this->getFilters();

        $transformedFilters = $this->transformInputFilters($filters);
        $statistics = $this->queue->getStatistics($transformedFilters);

        $this->assignCurrentRouteData('show-statistics', $filters);

        $this->viewData['filters'] = $filters;
        $this->viewData['statistics'] = $statistics;

        return $this->render();
    }
}
