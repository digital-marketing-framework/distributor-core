<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\Queue;

use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\Model\Queue\Job as OriginalJob;

class Job extends OriginalJob
{
    protected ?WriteableContextInterface $synchronousContext = null;

    public function getSynchronousContext(): ?WriteableContextInterface
    {
        return $this->synchronousContext;
    }

    public function setSynchronousContext(WriteableContextInterface $synchronousContext): void
    {
        $this->synchronousContext = $synchronousContext;
    }
}
