<?php

namespace DigitalMarketingFramework\Distributor\Core\Service;

use DigitalMarketingFramework\Core\Queue\WorkerInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;

interface RelayInterface extends WorkerInterface
{
    public const KEY_DISABLE_STORAGE = 'disableStorage';

    public const DEFAULT_DISABLE_STORAGE = true;

    public const KEY_ASYNC = 'async';

    public const DEFAULT_ASYNC = false;

    public function process(SubmissionDataSetInterface $submission): void;
}
