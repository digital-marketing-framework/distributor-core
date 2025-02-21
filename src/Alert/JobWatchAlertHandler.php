<?php

namespace DigitalMarketingFramework\Distributor\Core\Alert;

use DigitalMarketingFramework\Core\Alert\AlertHandler;
use DigitalMarketingFramework\Core\GlobalConfiguration\GlobalConfigurationAwareInterface;
use DigitalMarketingFramework\Core\GlobalConfiguration\GlobalConfigurationAwareTrait;
use DigitalMarketingFramework\Core\Model\Alert\AlertInterface;
use DigitalMarketingFramework\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Queue\GlobalConfiguration\Settings\QueueSettings;
use DigitalMarketingFramework\Distributor\Core\Registry\Registry;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface as DistributorRegistry;

class JobWatchAlertHandler extends AlertHandler implements GlobalConfigurationAwareInterface
{
    use GlobalConfigurationAwareTrait;

    protected DistributorRegistry $distributorRegistry;

    protected ?QueueSettings $queueSettings = null;

    public function __construct(
        string $keyword,
        RegistryInterface $registry,
    ) {
        parent::__construct($keyword, $registry);
        $this->distributorRegistry = $registry->getRegistryCollection()->getRegistryByClass(Registry::class);
    }

    protected function getQueueSettings(): QueueSettings
    {
        if (!$this->queueSettings instanceof QueueSettings) {
            $this->queueSettings = $this->globalConfiguration->getGlobalSettings(QueueSettings::class);
        }

        return $this->queueSettings;
    }

    /**
     * @param array<AlertInterface> $alerts
     */
    protected function checkStuckJobs(array &$alerts): void
    {
        $maxExecutionTime = $this->getQueueSettings()->getMaximumExecutionTime();
        $stuckJobs = $this->distributorRegistry->getPersistentQueue()->fetchPendingAndRunning(1, 0, $maxExecutionTime);
        if ($stuckJobs !== []) {
            $alerts[] = $this->createAlert('Some distributor jobs seem to be stuck.', 'Distributor', AlertInterface::TYPE_WARNING);
        }
    }

    /**
     * @param array<MessageInterface> $alerts
     */
    protected function checkFailedJobs(array &$alerts): void
    {
        $failedJobs = $this->distributorRegistry->getPersistentQueue()->fetchFailed(1);
        if ($failedJobs !== []) {
            $alerts[] = $this->createAlert('Failed distributor jobs detected.', 'Distributor', type: AlertInterface::TYPE_ERROR);
        }
    }

    public function getAlerts(): array
    {
        $alerts = [];
        $this->checkStuckJobs($alerts);
        $this->checkFailedJobs($alerts);

        return $alerts;
    }
}
