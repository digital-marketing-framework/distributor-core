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
     * @param array<string,array<string,mixed>> $dataMapperGroupConfigs
     * @param array<string,array<string,mixed>> $conditionConfigs
     * @param array<string,mixed> $config
     * @param array<string,mixed> $context
     */
    protected function createJob(array $data, array $routeConfigs, array $dataMapperGroupConfigs = [], array $conditionConfigs = [], array $config = [], array $context = [], string $jobRouteId = 'routeId1', string $jobRouteIntegrationName = 'integration1'): JobInterface
    {
        $data = [
            QueueDataFactory::KEY_ROUTE_ID => $jobRouteId,
            QueueDataFactory::KEY_INTEGRATION_NAME => $jobRouteIntegrationName,
            QueueDataFactory::KEY_SUBMISSION => [
                'data' => $data,
                'configuration' => $config,
                'context' => $context,
            ],
        ];

        $weight = 10;
        foreach ($routeConfigs as $routeId => $routeConfig) {
            $this->updateRouteConfiguration($routeConfig); // TODO located in SubmissionTestTrait, cleanup these test traits!
            $data[QueueDataFactory::KEY_SUBMISSION]['configuration']['integrations'][$jobRouteIntegrationName]['outboundRoutes'][$routeId] = static::createListItem([
                'type' => 'generic',
                'config' => [
                    'generic' => $routeConfig,
                ],
            ], $routeId, $weight);
            $weight += 10;
        }

        $weight = 10;
        foreach ($dataMapperGroupConfigs as $dataMapperGroupId => $dataMapperGroupConfig) {
            $data[QueueDataFactory::KEY_SUBMISSION]['configuration']['dataProcessing']['dataMapperGroups'][$dataMapperGroupId] = static::createMapItem(
                $dataMapperGroupId . 'Name',
                $dataMapperGroupConfig,
                $dataMapperGroupId,
                $weight
            );
            $weight += 10;
        }

        $weight = 10;
        foreach ($conditionConfigs as $conditionId => $conditionConfig) {
            $data[QueueDataFactory::KEY_SUBMISSION]['configuration']['dataProcessing']['conditions'][$conditionId] = static::createMapItem(
                $conditionId . 'Name',
                $conditionConfig,
                $conditionId,
                $weight
            );
            $weight += 10;
        }

        return new Job(data: $data);
    }
}
