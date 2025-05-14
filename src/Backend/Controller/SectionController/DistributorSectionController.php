<?php

namespace DigitalMarketingFramework\Distributor\Core\Backend\Controller\SectionController;

use DigitalMarketingFramework\Core\Backend\Controller\SectionController\SectionController;
use DigitalMarketingFramework\Core\Backend\Request;
use DigitalMarketingFramework\Core\Backend\Response\Response;
use DigitalMarketingFramework\Core\Registry\RegistryInterface;

class DistributorSectionController extends SectionController
{
    public function __construct(
        string $keyword,
        RegistryInterface $registry
    ) {
        parent::__construct($keyword, $registry, 'distributor', ['overview', 'list', 'edit', 'errors', 'preview', 'save', 'delete', 'queue', 'run']);
    }

    protected function overviewAction(Request $request): Response
    {
        return $this->render($request);
    }

    protected function listAction(Request $request): Response
    {
        return $this->render($request);
    }

    protected function editAction(Request $request): Response
    {
        return $this->render($request);
    }

    protected function errorsAction(Request $request): Response
    {
        return $this->render($request);
    }

    protected function previewAction(Request $request): Response
    {
        return $this->render($request);
    }

    protected function saveAction(Request $request): Response
    {
        $id = 42;
        return $this->redirect('page.distributor.edit', ['id' => $id]);
    }

    protected function deleteAction(Request $request): Response
    {
        return $this->redirect('page.distributor.list');
    }

    protected function queueAction(Request $request): Response
    {
        return $this->redirect('page.distributor.list');
    }

    protected function runAction(Request $request): Response
    {
        return $this->redirect('page.distributor.list');
    }
}
