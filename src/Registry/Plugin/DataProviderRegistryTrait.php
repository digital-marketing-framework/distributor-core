<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\ConfigurationDocument\SchemaDocument\Schema\Plugin\DataProvider\DataProviderSchema;
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

    protected function getDataProviderSchema(): SchemaInterface
    {
        $schema = new DataProviderSchema();
        foreach ($this->getAllPluginClasses(DataProviderInterface::class) as $key => $class) {
            $subSchema = $class::getSchema();
            $label = $class::getLabel();
            $schema->addItem($key, $subSchema, $label);
        }

        return $schema;
    }
}
