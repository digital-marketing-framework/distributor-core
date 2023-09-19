<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration;

use DigitalMarketingFramework\Core\Model\Queue\Job;
use DigitalMarketingFramework\Core\Model\Queue\JobInterface;
use DigitalMarketingFramework\Core\Tests\ListMapTestTrait;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory;

trait JobTestTrait // extends \PHPUnit\Framework\TestCase
{
    use ListMapTestTrait;

    /**
     * @param array<string,array{type:string,value:mixed}> $data
     * @param array<string,array<string,mixed>> $routeConfigs
     * @param array<string,mixed> $config
     * @param array<string,mixed> $context
     */
    protected function createJob(array $data, array $routeConfigs, array $config = [], array $context = [], string $jobRouteId = 'routeId1'): JobInterface
    {
        $data = [
            QueueDataFactory::KEY_ROUTE_ID => $jobRouteId,
            QueueDataFactory::KEY_SUBMISSION => [
                'data' => $data,
                'configuration' => $config,
                'context' => $context,
            ],
        ];
        $weight = 10;
        foreach ($routeConfigs as $routeId => $routeConfig) {
            $data[QueueDataFactory::KEY_SUBMISSION]['configuration']['distributor']['routes'][$routeId] = $this->createListItem([
                'type' => 'generic',
                'config' => [
                    'generic' => $routeConfig,
                ],
            ], $routeId, $weight);
            $weight += 10;
        }

        return new Job(data: $data);
    }
}
