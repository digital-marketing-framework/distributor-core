<?php

namespace DigitalMarketingFramework\Distributer\Core\Service;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Log\LoggerAwareInterface;
use DigitalMarketingFramework\Core\Log\LoggerAwareTrait;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Queue\QueueException;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Queue\WorkerInterface;
use DigitalMarketingFramework\Distributer\Core\Factory\QueueDataFactoryInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributer\Core\Registry\RegistryInterface;

class Relay implements RelayInterface, WorkerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const KEY_DISABLE_STORAGE = 'disableStorage';
    public const DEFAULT_DISABLE_STORAGE = true;

    public const KEY_ASYNC = 'async';
    public const DEFAULT_ASYNC = false;

    protected RegistryInterface $registry;

    protected QueueInterface $persistentQueue;

    protected QueueInterface $temporaryQueue;

    protected QueueDataFactoryInterface $queueDataFactory;

    protected array $enrichedSubmissionCache = [];

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
        $this->persistentQueue = $registry->getPersistentQueue();
        $this->temporaryQueue = $registry->getNonPersistentQueue();
        $this->queueDataFactory = $registry->getQueueDataFactory();
    }

    protected function addContext(SubmissionDataSetInterface $submission)
    {
        $request = $this->registry->getRequest();

        $dataProviders = $this->registry->getDataProviders();
        /** @var DataProviderInterface $dataProvider */
        foreach ($dataProviders as $dataProvider) {
            try {
                $dataProvider->addContext($submission, $request);
            } catch (DigitalMarketingFrameworkException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        $routes = $this->registry->getRoutes();
        /** @var RouteInterface $route */
        foreach ($routes as $route) {
            try {
                $passCount = $route->getPassCount($submission);
                for ($pass = 0; $pass < $passCount; $pass++) {
                    $route->addContext($submission, $request, $pass);
                }
            } catch (DigitalMarketingFrameworkException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    protected function processDataProviders(SubmissionDataSetInterface $submission): SubmissionDataSetInterface
    {
        $cacheKey = $this->queueDataFactory->getSubmissionCacheKey($submission);
        if (isset($this->enrichedSubmissionCache[$cacheKey])) {
            return $this->enrichedSubmissionCache[$cacheKey];
        }

        $dataProviders = $this->registry->getDataProviders();
        /** @var DataProviderInterface $dataProvider */
        foreach ($dataProviders as $dataProvider) {
            $dataProvider->addData($submission);
        }
        $this->enrichedSubmissionCache[$cacheKey] = $submission;
        return $submission;
    }

    public function processJob(JobInterface $job): bool
    {
        try {
            $submission = $this->queueDataFactory->convertJobToSubmission($job);
            $submission = $this->processDataProviders($submission);

            $routeName = $this->queueDataFactory->getJobRoute($job);
            $pass = $this->queueDataFactory->getJobRoutePass($job);

            /** @var RouteInterface|null $route */
            $route = $this->registry->getRoute($routeName);
            if (!$route) {
                throw new DigitalMarketingFrameworkException('route "' . $routeName . '" not found');
            }

            return $route->processPass($submission, $pass);

        } catch (DigitalMarketingFrameworkException $e) {
            $this->logger->error($e->getMessage());
            throw new QueueException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function process(SubmissionDataSetInterface $submission)
    {
        $this->addContext($submission);

        $syncPersistentJobs = [];
        $syncTemporaryJobs = [];
        $routes = $this->registry->getRoutes();
        /**
         * @var string $routeName
         * @var RouteInterface $route
         */
        foreach ($routes as $routeName => $route) {
            $passCount = $route->getPassCount($submission);
            for ($pass = 0; $pass < $passCount; $pass++) {
                $async = $submission->getConfiguration()->getWithRoutePassOverride(static::KEY_ASYNC, $routeName, $pass, static::DEFAULT_ASYNC);
                $disableStorage = $submission->getConfiguration()->getWithRoutePassOverride(static::KEY_DISABLE_STORAGE, $routeName, $pass, static::DEFAULT_DISABLE_STORAGE);

                if ($disableStorage && $async) {
                    $this->logger->error('Async submissions without storage are not possible. Using sync submission instead.');
                    $async = false;
                }

                $status = $async ? QueueInterface::STATUS_PENDING : QueueInterface::STATUS_RUNNING;
                $queue = $disableStorage ? $this->temporaryQueue : $this->persistentQueue;

                $job = $this->queueDataFactory->convertSubmissionToJob($submission, $routeName, $pass, $status);
                $queue->addJob($job);
                if (!$async) {
                    if ($disableStorage) {
                        $syncTemporaryJobs[] = $job;
                    } else {
                        $syncPersistentJobs[] = $job;
                    }
                }
            }
        }

        if (!empty($syncTemporaryJobs)) {
            $processor = $this->registry->getQueueProcessor($this->temporaryQueue, $this);
            $processor->processJobs($syncTemporaryJobs);
        }

        if (!empty($syncPersistentJobs)) {
            $processor = $this->registry->getQueueProcessor($this->persistentQueue, $this);
            $processor->processJobs($syncPersistentJobs);
        }
    }

    public static function getDefaultConfiguration(): array
    {
        return [
            static::KEY_ASYNC => static::DEFAULT_ASYNC,
            static::KEY_DISABLE_STORAGE => static::DEFAULT_DISABLE_STORAGE,
        ];
    }
}
