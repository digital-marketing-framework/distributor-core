<?php

namespace DigitalMarketingFramework\Distributor\Core\Backend\Controller\SectionController;

use DigitalMarketingFramework\Core\Backend\Response\Response;
use DigitalMarketingFramework\Core\Model\Queue\Error;
use DigitalMarketingFramework\Core\Registry\RegistryInterface;

/**
 * @extends DistributorSectionController<Error>
 */
class DistributorErrorMonitorSectionController extends DistributorSectionController
{
    public function __construct(
        string $keyword,
        RegistryInterface $registry,
    ) {
        parent::__construct($keyword, $registry, ['show-errors']);
    }

    protected function fetchFilteredCount(array $filters): int
    {
        return count($this->queue->getErrorMessages($filters, [
            'sorting' => [],
            'page' => 0,
            'itemsPerPage' => 0,
        ]));
    }

    protected function fetchFiltered(array $filters, array $navigation): array
    {
        return $this->queue->getErrorMessages($filters, $navigation);
    }

    protected function showErrorsAction(): Response
    {
        $this->setUpListView(['count' => 'DESC', 'lastSeen' => 'DESC', 'firstSeen' => '']);

        return $this->render();
    }
}
