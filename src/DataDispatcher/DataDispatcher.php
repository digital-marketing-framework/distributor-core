<?php

namespace DigitalMarketingFramework\Distributor\Core\DataDispatcher;

use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\Plugin\Plugin;

abstract class DataDispatcher extends Plugin implements DataDispatcherInterface
{
    /**
     * @param array<string,string|ValueInterface> $data
     *
     * @return array<string,string|ValueInterface>
     */
    protected function transformDataForPreview(array $data): array
    {
        return $data;
    }

    /**
     * @param array<string,string|ValueInterface> $data
     *
     * @return array<string,mixed>
     */
    public function preview(array $data): array
    {
        return [
            'dataDispatcher' => $this,
            'keyword' => GeneralUtility::camelCaseToDashed($this->getKeyword()),
            'class' => static::class,
            'config' => [],
            'data' => $this->transformDataForPreview($data),
        ];
    }
}
