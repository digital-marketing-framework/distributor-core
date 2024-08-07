<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryTrait;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProviderInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\SchemaDocument\RenderingDefinition\Icon;
use DigitalMarketingFramework\Distributor\Core\SchemaDocument\Schema\Plugin\DataProvider\DataProviderSchema;

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
        $schema->getRenderingDefinition()->setLabel('Additional Data');
        $schema->getRenderingDefinition()->setIcon(Icon::DATA_PROVIDERS);
        $schema->getRenderingDefinition()->setGeneralDescription('Additional data can be provided for outbound routes, like form submissions. The data is derived from the context of the request that triggered the route, like the website language or the timestamp or request cookies or headers.');
        $schema->getRenderingDefinition()->setHint('Data providers are configured globally, but you can enable or disable them individually for each outbound route.');
        foreach ($this->getAllPluginClasses(DataProviderInterface::class) as $key => $class) {
            $subSchema = $class::getSchema();
            $label = $class::getLabel();
            $schema->addItem($key, $subSchema, $label);
        }

        return $schema;
    }
}
