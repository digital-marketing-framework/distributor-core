<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration;

use DigitalMarketingFramework\Core\Model\Queue\Job;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory;

trait JobTestTrait // extends \PHPUnit\Framework\TestCase
{
    protected function createJob($data, $genericRouteConfig, $pass = 0, $config = [], $context = []): JobInterface
    {
        $data = [
            QueueDataFactory::KEY_ROUTE => 'generic',
            QueueDataFactory::KEY_PASS => $pass,
            QueueDataFactory::KEY_SUBMISSION => [
                'data' => $data,
                'configuration' => $config,
                'context' => $context,
            ]
        ];
        $data[QueueDataFactory::KEY_SUBMISSION]['configuration']['distributor']['routes']['generic'] = $genericRouteConfig;
        return new Job(data:$data);
    }
}
