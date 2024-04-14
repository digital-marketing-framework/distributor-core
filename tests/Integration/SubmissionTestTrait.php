<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Integration;

use DigitalMarketingFramework\Core\Model\Configuration\ConfigurationInterface;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Core\Tests\ListMapTestTrait;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\SubmissionConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Service\Relay;

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
            'distributor' => [
                Relay::KEY_ASYNC => false,
                SubmissionConfigurationInterface::KEY_DATA_PROVIDERS => [],
                SubmissionConfigurationInterface::KEY_ROUTES => [],
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
     * @param array<string,mixed> $configuration
     */
    protected function addStreamConfiguration(string $streamName, string $streamId, int $weight, array $configuration, int $index = 0): void
    {
        $this->submissionConfiguration[$index][ConfigurationInterface::KEY_STREAMS][$streamId] = $this->createMapItem($streamName, $configuration, $streamId, $weight);
    }

    /**
     * @param array<string,mixed> $configuration
     */
    protected function addRouteConfiguration(string $routeName, string $routeId, int $weight, array $configuration, int $index = 0): void
    {
        $this->submissionConfiguration[$index]['distributor'][SubmissionConfigurationInterface::KEY_ROUTES][$routeId] = $this->createListItem([
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
        $this->submissionConfiguration[$index]['distributor'][SubmissionConfigurationInterface::KEY_DATA_PROVIDERS][$name] = $configuration;
    }

    protected function setSubmissionAsync(bool $async = true, int $index = 0): void
    {
        $this->submissionConfiguration[$index]['distributor'][Relay::KEY_ASYNC] = $async;
    }

    protected function setStorageDisabled(bool $disableStorage = false, int $index = 0): void
    {
        $this->submissionConfiguration[$index]['distributor'][Relay::KEY_DISABLE_STORAGE] = $disableStorage;
    }
}
