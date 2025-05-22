<?php

namespace DigitalMarketingFramework\Distributor\Core\Backend\Controller\SectionController;

use DateTime;
use DigitalMarketingFramework\Core\Backend\Controller\SectionController\SectionController;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Queue\GlobalConfiguration\Settings\QueueSettings;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface as DistributorRegistryInterface;

abstract class DistributorSectionController extends SectionController
{
    protected const DISTRIBUTOR_LIST_SCRIPT = 'PKG:digital-marketing-framework/distributor-core/res/assets/scripts/backend/distributor-list.js';

    protected DistributorRegistryInterface $distributorRegistry;

    protected QueueSettings $queueSettings;

    protected QueueInterface $queue;

    public function __construct(string $keyword, RegistryInterface $registry, array $routes)
    {
        parent::__construct($keyword, $registry, 'distributor', $routes);

        $this->distributorRegistry = $registry->getRegistryCollection()->getRegistryByClass(DistributorRegistryInterface::class);
        $this->queueSettings = $registry->getGlobalConfiguration()->getGlobalSettings(QueueSettings::class);
        $this->queue = $this->distributorRegistry->getPersistentQueue();
    }

    protected function addDistributorListScript(): void
    {
        $this->addScript(static::DISTRIBUTOR_LIST_SCRIPT, 'distributor-list');
    }

    /**
     * @return array{search?:string,advancedSearch?:bool,searchExactMatch?:bool,minCreated?:string,maxCreated?:string,minChanged?:string,maxChanged?:string,type?:array<string,string>,status?:array<string>} $filters
     */
    protected function getFilters(): array
    {
        return $this->getParameters()['filters'] ?? [];
    }

    /**
     * @return array{page?:int|string,itemsPerPage?:int|string,sorting?:array<string,string>} $navigation
     */
    protected function getNavigation(): array
    {
        return $this->getParameters()['navigation'] ?? [];
    }

    /**
     * @return array<string|int,string|int>
     */
    protected function getList(): array
    {
        $list = $this->getParameters()['list'] ?? [];

        return array_values(array_filter($list));
    }

    protected function getPage(): ?int
    {
        return $this->getParameters()['page'] ?? null;
    }

    protected function getCurrentAction(string $default): string
    {
        return $this->getParameters()['currentAction'] ?? $default;
    }

    /**
     * @param array<string,mixed> $arguments
     */
    protected function cleanupArguments(array &$arguments): void
    {
        // TODO can we filter out default values in addition to empty values?
        foreach (array_keys($arguments) as $key) {
            if (is_array($arguments[$key])) {
                $this->cleanupArguments($arguments[$key]);
                if ($arguments[$key] === []) {
                    unset($arguments[$key]);
                }
            } elseif ($arguments[$key] === '') {
                unset($arguments[$key]);
            }
        }
    }

    /**
     * @param array{search?:string,advancedSearch?:bool,searchExactMatch?:bool,minCreated?:string,maxCreated?:string,minChanged?:string,maxChanged?:string,type?:array<string,string>,status?:array<string>} $filters
     * @param array{page?:int|string,itemsPerPage?:int|string,sorting?:array<string,string>} $navigation
     */
    protected function getPermanentUri(string $action, array $filters = [], array $navigation = []): string
    {
        $arguments = ['filters' => $filters, 'navigation' => $navigation];
        $this->cleanupArguments($arguments['filters']);

        return $this->uriBuilder->build('page.distributor.' . $action, $arguments);
    }

    /**
     * @param array{search?:string,advancedSearch?:bool,searchExactMatch?:bool,minCreated?:string,maxCreated?:string,minChanged?:string,maxChanged?:string,type?:array<string,string>,status?:array<string>} $filters
     * @param array{page?:int|string,itemsPerPage?:int|string,sorting?:array<string,string>} $navigation
     */
    protected function assignCurrentRouteData(string $defaultAction, array $filters = [], array $navigation = []): void
    {
        $currentAction = $this->getCurrentAction($defaultAction);
        $this->viewData['current'] = $currentAction;

        $permanentUri = $this->getPermanentUri($defaultAction, $filters, $navigation);
        $this->viewData['permanentUri'] = $permanentUri;

        $resetUri = $this->getPermanentUri($defaultAction);
        $this->viewData['resetUri'] = $resetUri;
    }

    /**
     * @param array{search?:string,advancedSearch?:bool,searchExactMatch?:bool,minCreated?:string,maxCreated?:string,minChanged?:string,maxChanged?:string,type?:array<string,string>,status?:array<string>} $filters
     *
     * @return array{search:string,advancedSearch:bool,searchExactMatch:bool,minCreated:?DateTime,maxCreated:?DateTime,minChanged:?DateTime,maxChanged:?DateTime,type:array<string>,status:array<int>,skipped:?bool}
     */
    protected function transformInputFilters(array $filters): array
    {
        $result = [
            'search' => $filters['search'] ?? '',
            'advancedSearch' => $filters['advancedSearch'] ?? false,
            'searchExactMatch' => $filters['searchExactMatch'] ?? false,
            'minCreated' => isset($filters['minCreated']) && $filters['minCreated'] !== '' ? new DateTime($filters['minCreated']) : null,
            'maxCreated' => isset($filters['maxCreated']) && $filters['maxCreated'] !== '' ? new DateTime($filters['maxCreated']) : null,
            'minChanged' => isset($filters['minChanged']) && $filters['minChanged'] !== '' ? new DateTime($filters['minChanged']) : null,
            'maxChanged' => isset($filters['maxChanged']) && $filters['maxChanged'] !== '' ? new DateTime($filters['maxChanged']) : null,
            'type' => isset($filters['type']) ? array_keys(array_filter($filters['type'])) : [],
        ];

        $result['status'] = [];
        $result['skipped'] = null;

        $inputStatus = isset($filters['status']) ? array_keys(array_filter($filters['status'])) : [];
        $skippedFound = false;
        $notSkippedFound = false;
        foreach ($inputStatus as $status) {
            switch ($status) {
                case 'queued':
                    $result['status'][] = QueueInterface::STATUS_QUEUED;
                    break;
                case 'pending':
                    $result['status'][] = QueueInterface::STATUS_PENDING;
                    break;
                case 'running':
                    $result['status'][] = QueueInterface::STATUS_RUNNING;
                    break;
                case 'doneNotSkipped':
                    $result['status'][] = QueueInterface::STATUS_DONE;
                    $notSkippedFound = true;
                    break;
                case 'doneSkipped':
                    $result['status'][] = QueueInterface::STATUS_DONE;
                    $skippedFound = true;
                    break;
                case 'failed':
                    $result['status'][] = QueueInterface::STATUS_FAILED;
                    break;
            }
        }

        if (!$skippedFound && $notSkippedFound) {
            $result['skipped'] = false;
        } elseif ($skippedFound && !$notSkippedFound) {
            $result['skipped'] = true;
        }

        return $result;
    }

    /**
     * @param array{page?:int|string,itemsPerPage?:int|string,sorting?:array<string,string>} $navigation
     * @param array<string,string> $defaultSorting
     *
     * @return array{page:int,itemsPerPage:int,sorting:array<string,string>}
     */
    protected function transformInputNavigation(array $navigation, array $defaultSorting): array
    {
        return [
            'page' => (int)($navigation['page'] ?? 0),
            'itemsPerPage' => (int)($navigation['itemsPerPage'] ?? 20),
            'sorting' => $navigation['sorting'] ?? $defaultSorting,
        ];
    }
}
