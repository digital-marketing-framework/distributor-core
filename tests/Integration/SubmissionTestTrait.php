<?php

namespace DigitalMarketingFramework\Distributer\Core\Tests\Integration;

use DigitalMarketingFramework\Distributer\Core\Model\Configuration\SubmissionConfigurationInterface;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSet;
use DigitalMarketingFramework\Distributer\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributer\Core\Service\Relay;

trait SubmissionTestTrait // extends \PHPUnit\Framework\TestCase
{
    protected array $submissionData = [];

    protected array $submissionConfiguration = [];

    protected array $submissionContext = [];

    protected function baseConfiguration(): array
    {
        return [
            Relay::KEY_ASYNC => false,
            SubmissionConfigurationInterface::KEY_DATA_PROVIDERS => [],
            SubmissionConfigurationInterface::KEY_ROUTES => [],
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

    protected function addRouteConfiguration(string $name, array $configuration, int $index = 0): void
    {
        $this->submissionConfiguration[$index][SubmissionConfigurationInterface::KEY_ROUTES][$name] = $configuration;
    }

    protected function addDataProviderConfiguration(string $name, array $configuration, int $index = 0): void
    {
        $this->submissionConfiguration[$index][SubmissionConfigurationInterface::KEY_DATA_PROVIDERS][$name] = $configuration;
    }

    protected function setSubmissionAsync(bool $async = true, int $index = 0): void
    {
        $this->submissionConfiguration[$index][Relay::KEY_ASYNC] = $async;
    }

    protected function setStorageDisabled(bool $disableStorage = false, int $index = 0): void
    {
        $this->submissionConfiguration[$index][Relay::KEY_DISABLE_STORAGE] = $disableStorage;
    }
}
