<?php

namespace DigitalMarketingFramework\Distributor\Core\Backend\Controller\SectionController;

use DateTime;
use DigitalMarketingFramework\Core\Backend\Controller\SectionController\ListSectionController;
use DigitalMarketingFramework\Core\Model\ItemInterface;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Queue\GlobalConfiguration\Settings\QueueSettings;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface as DistributorRegistryInterface;

/**
 * @template ItemClass of ItemInterface
 *
 * @extends ListSectionController<ItemClass>
 */
abstract class DistributorSectionController extends ListSectionController
{
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

    protected function fetchFilteredCount(array $filters): int
    {
        return $this->queue->countFiltered($filters);
    }

    protected function fetchFiltered(array $filters, array $navigation): array
    {
        return $this->queue->fetchFiltered($filters, $navigation);
    }

    /**
     * @param array{search?:string,advancedSearch?:bool,minCreated?:string,maxCreated?:string,minChanged?:string,maxChanged?:string,type?:array<string,string>,status?:array<string>} $filters
     *
     * @return array{search:string,advancedSearch:bool,minCreated:?DateTime,maxCreated:?DateTime,minChanged:?DateTime,maxChanged:?DateTime,type:array<string>,status:array<int>,skipped:?bool}
     */
    protected function transformInputFilters(array $filters): array
    {
        $result = [
            'search' => $filters['search'] ?? '',
            'advancedSearch' => $filters['advancedSearch'] ?? false,
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
}
