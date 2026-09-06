<?php

namespace DigitalMarketingFramework\Distributor\Core\DataDispatcher;

use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\Plugin\Plugin;

abstract class DataDispatcher extends Plugin implements DataDispatcherInterface
{
    public function dispatch(DataInterface $data): void
    {
        $this->send($data->toArray());
    }

    public function previewDispatch(DataInterface $data): array
    {
        return $this->preview($data->toArray());
    }

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
     * @deprecated override previewDispatch() instead
     * @see self::previewDispatch()
     *
     * @param array<string,string|ValueInterface> $data
     *
     * @return array<string,mixed>
     */
    public function preview(array $data): array
    {
        return [
            'keyword' => GeneralUtility::camelCaseToDashed($this->getKeyword()),
            'config' => [],
            'data' => $this->transformDataForPreview($data),
        ];
    }
}
