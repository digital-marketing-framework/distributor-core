<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration;

use DigitalMarketingFramework\Core\Model\Configuration\ConfigurationInterface;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\RestrictedTermsSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SwitchSchema;
use DigitalMarketingFramework\Core\Tests\ListMapTestTrait;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRoute;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRouteInterface;

trait SubmissionTestTrait // extends \PHPUnit\Framework\TestCase
{
    use ListMapTestTrait;

    /** @var array<string,string|ValueInterface> */
    protected array $submissionData = [];

    /** @var array<int,array<string,mixed>> */
    protected array $submissionConfiguration = [];

    /** @var array<string,mixed> */
    protected array $submissionContext = [];

    /**
     * @return array<string,mixed>
     */
    protected function baseConfiguration(): array
    {
        return [
            'integrations' => [
                'general' => [
                    'outboundRoutes' => [
                        DistributorConfigurationInterface::KEY_ASYNC => DistributorConfigurationInterface::DEFAULT_ASYNC,
                        DistributorConfigurationInterface::KEY_ENABLE_STORAGE => DistributorConfigurationInterface::DEFAULT_ENABLE_STORAGE,
                    ],
                ],
            ],
        ];
    }

    protected function initSubmission(): void
    {
        $this->submissionData = [];
        $this->submissionConfiguration = [$this->baseConfiguration()];
        $this->submissionContext = [];
    }

    protected function getSubmission(): SubmissionDataSetInterface
    {
        return new SubmissionDataSet($this->submissionData, $this->submissionConfiguration, $this->submissionContext);
    }

    /**
     * @return array{type:string,config:array<string,array<string,mixed>>}
     */
    protected function getStaticConditionConfiguration(bool $succeed = true): array
    {
        return $this->getConditionConfiguration($succeed ? 'true' : 'false', []);
    }

    /**
     * @param array<mixed> $dataMapperConfig
     *
     * @return array{type:string,config:array{single:array<mixed>}}
     */
    protected function getDataMapperGroupConfiguration(array $dataMapperConfig): array
    {
        return [
            'type' => 'single',
            'config' => [
                'single' => $dataMapperConfig,
            ],
        ];
    }

    /**
     * @return array{type:string,config:array{single:array{passthroughField:array{enabled:bool}}}}
     */
    protected function getPassthroughDataMapperGroupConfiguration(bool $enabled = true): array
    {
        return $this->getDataMapperGroupConfiguration([
            'data' => [
                'passthroughFields' => [
                    'enabled' => $enabled,
                ],
            ],
        ]);
    }

    protected function configurePassthroughDataMapperGroup(string $dataMapperGroupId): void
    {
        $this->addDataMapperGroupConfiguration($dataMapperGroupId . 'Name', $dataMapperGroupId, 0, $this->getPassthroughDataMapperGroupConfiguration());
    }

    /**
     * @param array<string,mixed> $configuration
     */
    protected function addDataMapperGroupConfiguration(string $dataMapperGroupName, string $dataMapperGroupId, int $weight, array $configuration, int $index = 0): void
    {
        $this->submissionConfiguration[$index][ConfigurationInterface::KEY_DATA_PROCESSING][ConfigurationInterface::KEY_DATA_MAPPER_GROUPS][$dataMapperGroupId] = static::createMapItem(
            $dataMapperGroupName,
            $configuration,
            $dataMapperGroupId,
            $weight
        );
    }

    /**
     * @param array<string,mixed> $configuration
     */
    protected function updateRouteConfiguration(array &$configuration): void
    {
        if (!isset($configuration[OutboundRoute::KEY_ENABLE_DATA_PROVIDERS])) {
            $configuration[OutboundRoute::KEY_ENABLE_DATA_PROVIDERS] = [
                SwitchSchema::KEY_TYPE => RestrictedTermsSchema::KEY_ALL,
                SwitchSchema::KEY_CONFIG => [
                    RestrictedTermsSchema::KEY_ALL => [],
                ],
            ];
        }

        if (!isset($configuration[OutboundRouteInterface::KEY_GATE])) {
            $configuration[OutboundRouteInterface::KEY_GATE] = [
                SwitchSchema::KEY_TYPE => 'true',
                SwitchSchema::KEY_CONFIG => [
                    'true' => [],
                ],
            ];
        }
    }

    /**
     * @param array<string,mixed> $configuration
     */
    protected function addRouteConfiguration(string $routeName, string $routeId, int $weight, array $configuration, int $index = 0, string $integrationName = 'integration1'): void
    {
        $this->updateRouteConfiguration($configuration);
        $this->submissionConfiguration[$index]['integrations'][$integrationName][DistributorConfigurationInterface::KEY_OUTBOUND_ROUTES][$routeId] = static::createListItem([
            'type' => $routeName,
            'pass' => '',
            'config' => [
                $routeName => $configuration,
            ],
        ], $routeId, $weight);
    }

    /**
     * @param array<string,mixed> $configuration
     */
    protected function addDataProviderConfiguration(string $name, array $configuration, int $index = 0): void
    {
        $this->submissionConfiguration[$index]['dataProcessing'][DistributorConfigurationInterface::KEY_DATA_PROVIDERS][$name] = $configuration;
    }

    protected function setSubmissionAsync(bool $async = true, int $index = 0): void
    {
        $this->submissionConfiguration[$index]['integrations']['general']['outboundRoutes'][DistributorConfigurationInterface::KEY_ASYNC] = $async;
    }

    protected function setStorageEnabled(bool $enableStorage = true, int $index = 0): void
    {
        $this->submissionConfiguration[$index]['integrations']['general']['outboundRoutes'][DistributorConfigurationInterface::KEY_ENABLE_STORAGE] = $enableStorage;
    }
}
