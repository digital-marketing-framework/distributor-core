<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryInterface;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProviderInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;

interface DataProviderRegistryInterface extends PluginRegistryInterface
{
    public function registerDataProvider(string $class, array $additionalArguments = [], string $keyword = ''): void;
    public function getDataProvider(string $keyword, SubmissionDataSetInterface $submission): ?DataProviderInterface;
    public function getDataProviders(SubmissionDataSetInterface $submission): array;
    public function deleteDataProvider(string $keyword): void;
    
    public function getDataProviderDefaultConfigurations(): array;
    public function getDataProviderSchema(): SchemaInterface;
}
