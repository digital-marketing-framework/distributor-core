<?php

namespace DigitalMarketingFramework\Distributor\Core\Backend\Controller\SectionController;

use BadMethodCallException;
use DateTime;
use DigitalMarketingFramework\Core\Backend\Response\RedirectResponse;
use DigitalMarketingFramework\Core\Backend\Response\Response;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Registry\RegistryInterface;

/**
 * @extends DistributorSectionController<JobInterface>
 */
class DistributorListSectionController extends DistributorSectionController
{
    protected const PAGINATION_ITEMS_EACH_SIDE = 3;

    protected const DATE_TIME_FORMAT = 'Y-m-d\\TH:i';

    /**
     * @param array<string> $routes
     */
    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        array $routes = [],
    ) {
        parent::__construct($keyword, $registry, ['list', 'list-expired', 'list-stuck', 'list-failed', 'preview', 'queue', 'run', 'delete', 'edit', 'save', ...$routes]);
    }

    protected function getExpirationDate(): DateTime
    {
        $expirationTime = $this->queueSettings->getExpirationTime();
        $expirationDate = new DateTime();
        $expirationDate->modify('-' . $expirationTime . ' days');

        return $expirationDate;
    }

    protected function getStuckDate(): DateTime
    {
        $maxExecutionTime = $this->queueSettings->getMaximumExecutionTime();
        $stuckDate = new DateTime();
        $stuckDate->modify('-' . $maxExecutionTime . ' seconds');

        return $stuckDate;
    }

    /**
     * @param array{search:string,advancedSearch:bool,minCreated:?DateTime,maxCreated:?DateTime,minChanged:?DateTime,maxChanged:?DateTime,type:array<string>,status:array<int>,skipped:?bool} $filters
     *
     * @return array<string,int>
     */
    protected function getTypeFilterBounds(array $filters): array
    {
        $types = [];
        $allTypes = $this->queue->fetchJobTypes();
        $allTypes = array_merge($allTypes, $filters['type']);
        foreach ($allTypes as $type) {
            $typeFilters = $filters;
            $typeFilters['type'] = [$type];
            $count = $this->queue->countFiltered($typeFilters);
            $types[$type] = $count;
        }

        return $types;
    }

    /**
     * @param array{search:string,advancedSearch:bool,minCreated:?DateTime,maxCreated:?DateTime,minChanged:?DateTime,maxChanged:?DateTime,type:array<string>,status:array<int>,skipped:?bool} $filters
     *
     * @return array<string,int>
     */
    protected function getStatusFilterBounds(array $filters): array
    {
        $statusValues = [];
        foreach (['queued', 'pending', 'running', 'doneNotSkipped', 'doneSkipped', 'failed'] as $status) {
            $statusFilters = $filters;
            switch ($status) {
                case 'queued':
                    $statusFilters['status'] = [QueueInterface::STATUS_QUEUED];
                    $statusFilters['skipped'] = null;
                    break;
                case 'pending':
                    $statusFilters['status'] = [QueueInterface::STATUS_PENDING];
                    $statusFilters['skipped'] = null;
                    break;
                case 'running':
                    $statusFilters['status'] = [QueueInterface::STATUS_RUNNING];
                    $statusFilters['skipped'] = null;
                    break;
                case 'doneNotSkipped':
                    $statusFilters['status'] = [QueueInterface::STATUS_DONE];
                    $statusFilters['skipped'] = false;
                    break;
                case 'doneSkipped':
                    $statusFilters['status'] = [QueueInterface::STATUS_DONE];
                    $statusFilters['skipped'] = true;
                    break;
                case 'failed':
                    $statusFilters['status'] = [QueueInterface::STATUS_FAILED];
                    $statusFilters['skipped'] = null;
                    break;
            }

            $count = $this->queue->countFiltered($statusFilters);
            $statusValues[$status] = $count;
        }

        return $statusValues;
    }

    /**
     * @param array{search:string,advancedSearch:bool,minCreated:?DateTime,maxCreated:?DateTime,minChanged:?DateTime,maxChanged:?DateTime,type:array<string>,status:array<int>,skipped:?bool} $filters
     *
     * @return array{type:array<string,int>,status:array<string,int>,countNotEmpty:array{type:int,status:int},selected:array{type:bool,status:bool}}
     */
    protected function getFilterBounds(array $filters): array
    {
        $types = $this->getTypeFilterBounds($filters);
        $status = $this->getStatusFilterBounds($filters);

        $typeCountNotEmpty = count(array_filter($types, static fn (int $count) => $count > 0));
        $typeSelected = $filters['type'] !== [];

        $statusCountNotEmpty = count(array_filter($status, static fn (int $count) => $count > 0));
        $statusSelected = $filters['status'] !== [];

        return [
            'type' => $types,
            'status' => $status,
            'countNotEmpty' => [
                'type' => $typeCountNotEmpty,
                'status' => $statusCountNotEmpty,
            ],
            'selected' => [
                'type' => $typeSelected,
                'status' => $statusSelected,
            ],
        ];
    }

    protected function listAction(): Response
    {
        $this->setUpListView(['changed' => 'DESC', 'created' => '', 'type' => '', 'status' => '']);

        $this->viewData['expirationDate'] = $this->getExpirationDate();
        $this->viewData['maxExecutionTime'] = $this->queueSettings->getMaximumExecutionTime();
        $this->viewData['stuckDate'] = $this->getStuckDate();

        return $this->render();
    }

    protected function listStuckAction(): Response
    {
        $maxChanged = $this->getStuckDate();

        return $this->redirect('page.distributor.list', [
            'currentAction' => 'list-stuck',
            'filters' => [
                'maxChanged' => $maxChanged->format(static::DATE_TIME_FORMAT),
                'status' => ['queued' => 1, 'pending' => 1, 'running' => 1],
            ],
        ]);
    }

    protected function listExpiredAction(): Response
    {
        $maxChanged = $this->getExpirationDate();

        return $this->redirect('page.distributor.list', [
            'currentAction' => 'list-expired',
            'filters' => [
                'maxChanged' => $maxChanged->format(static::DATE_TIME_FORMAT),
                'status' => ['doneNotSkipped' => '1', 'doneSkipped' => '1'],
            ],
        ]);
    }

    protected function listFailedAction(): Response
    {
        return $this->redirect('page.distributor.list', [
            'currentAction' => 'list-failed',
            'filters' => [
                'status' => ['failed' => 1],
            ],
        ]);
    }

    protected function editAction(): never
    {
        throw new BadMethodCallException('Distributor edit action not implemented in core package');
    }

    protected function saveAction(): never
    {
        throw new BadMethodCallException('Distributor save action not implemented in core package');
    }

    protected function previewAction(): Response
    {
        $list = $this->getSelectedItems();
        $records = [];
        if ($list !== []) {
            $jobs = $this->queue->fetchByIdList($list);
            $distributor = $this->distributorRegistry->getDistributor();
            foreach ($jobs as $job) {
                $records[] = [
                    'job' => $job,
                    'preview' => $distributor->getPreviewData($job),
                ];
            }
        }

        $this->assignCurrentRouteData();
        $this->viewData['records'] = $records;

        return $this->render();
    }

    protected function deleteAction(): Response
    {
        $list = $this->getSelectedItems();
        $returnUrl = $this->getReturnUrl($this->uriBuilder->build('page.distributor.list'));

        if ($list !== []) {
            $jobs = $this->queue->fetchByIdList($list);
            foreach ($jobs as $job) {
                $this->queue->remove($job);
            }
        }

        return new RedirectResponse($returnUrl);
    }

    protected function queueAction(): Response
    {
        $list = $this->getSelectedItems();
        $returnUrl = $this->getReturnUrl($this->uriBuilder->build('page.distributor.list'));

        if ($list !== []) {
            $jobs = $this->queue->fetchByIdList($list);
            $this->queue->markListAsQueued($jobs);
        }

        return new RedirectResponse($returnUrl);
    }

    protected function runAction(): Response
    {
        $list = $this->getSelectedItems();
        $returnUrl = $this->getReturnUrl($this->uriBuilder->build('page.distributor.list'));

        if ($list !== []) {
            $jobs = $this->queue->fetchByIdList($list);
            $worker = $this->distributorRegistry->getQueueProcessor(
                $this->queue,
                $this->distributorRegistry->getDistributor()
            );
            $worker->processJobs($jobs);
        }

        return new RedirectResponse($returnUrl);
    }
}
