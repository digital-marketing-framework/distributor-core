<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry\Plugin;

use DigitalMarketingFramework\Core\Registry\Plugin\PluginRegistryTrait;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;

trait DataDispatcherRegistryTrait
{
    use PluginRegistryTrait;

    public function registerDataDispatcher(string $class, array $additionalArguments = [], string $keyword = ''): void
    {
        $this->registerPlugin(DataDispatcherInterface::class, $class, $additionalArguments, $keyword);
    }

    public function getDataDispatchers(): array
    {
        /** @var array<DataDispatcherInterface> */
        return $this->getAllPlugins(DataDispatcherInterface::class);
    }

    public function getDataDispatcher(string $keyword): ?DataDispatcherInterface
    {
        /** @var ?DataDispatcherInterface */
        return $this->getPlugin($keyword, DataDispatcherInterface::class);
    }

    public function deleteDataDispatcher(string $keyword): void
    {
        $this->deletePlugin($keyword, DataDispatcherInterface::class);
    }
}
