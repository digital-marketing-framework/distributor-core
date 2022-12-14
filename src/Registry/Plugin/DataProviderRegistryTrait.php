<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProviderInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;

trait DataProviderRegistryTrait
{
    use PluginRegistryTrait;

    public function registerDataProvider(string $class, array $additionalArguments = [], string $keyword = ''): void
    {
        $this->registerPlugin(DataProviderInterface::class, $class, $additionalArguments, $keyword);
    }
    
    public function getDataProvider(string $keyword, SubmissionDataSetInterface $submission): ?DataProviderInterface
    {
        return $this->getPlugin($keyword, DataProviderInterface::class, [$submission]);
    }

    public function getDataProviders(SubmissionDataSetInterface $submission): array
    {
        return $this->getAllPlugins(DataProviderInterface::class, [$submission]);
    }

    public function deleteDataProvider(string $keyword): void
    {
        $this->deletePlugin($keyword, DataProviderInterface::class);
    }

    public function getDataProviderDefaultConfigurations(): array
    {
        $result = [];
        foreach ($this->pluginClasses[DataProviderInterface::class] ?? [] as $key => $class) {
            $result[$key] = $class::getDefaultConfiguration();
        }
        return $result;
    }
}
