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
        parent::__construct($keyword, $registry, ['show-statistics', 'show-errors']);
    }

    protected function showStatisticsAction(): Response
    {
        $this->addDistributorListScript();

        $filters = $this->getFilters();

        $transformedFilters = $this->transformInputFilters($filters);
        $statistics = $this->queue->getStatistics($transformedFilters);

        $this->assignCurrentRouteData('show-statistics', $filters);

        $this->viewData['filters'] = $filters;
        $this->viewData['statistics'] = $statistics;

        return $this->render();
    }

    protected function showErrorsAction(): Response
    {
        $this->addDistributorListScript();

        $filters = $this->getFilters();
        $navigation = $this->getNavigation();

        $transformedFilters = $this->transformInputFilters($filters);
        $transformedNavigation = $this->transformInputNavigation($navigation, defaultSorting: ['count' => 'DESC', 'lastSeen' => 'DESC', 'firstSeen' => '']);

        $navigationBounds = [
            'sort' => ['count', 'firstSeen', 'lastSeen'],
            'sortDirection' => ['', 'ASC', 'DESC'],
        ];

        $this->assignCurrentRouteData('show-errors', $filters, $transformedNavigation);

        $this->viewData['current'] = 'show-errors';
        $this->viewData['filters'] = $filters;
        $this->viewData['navigation'] = $transformedNavigation;
        $this->viewData['navigationBounds'] = $navigationBounds;

        $errors = $this->queue->getErrorMessages($transformedFilters, $transformedNavigation);
        $this->viewData['errors'] = $errors;

        return $this->render();
    }
}
