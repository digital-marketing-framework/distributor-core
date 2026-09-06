<?php

namespace DigitalMarketingFramework\Distributor\Core\DataDispatcher;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Core\Plugin\PluginInterface;

interface DataDispatcherInterface extends PluginInterface
{
    /**
     * Sends the mapped data to this dispatcher's target.
     *
     * This is what routes call. Dispatchers that only need the field values override
     * send() instead and inherit the default implementation from DataDispatcher.
     *
     * @throws DigitalMarketingFrameworkException
     */
    public function dispatch(DataInterface $data): void;

    /**
     * Describes what dispatch() would send, without sending it.
     *
     * @return array<string,mixed>
     *
     * @throws DigitalMarketingFrameworkException
     */
    public function previewDispatch(DataInterface $data): array;

    /**
     * Receives the field values without the surrounding data object.
     *
     * @deprecated Override dispatch() instead, which receives the data object itself.
     *             Still called by the default dispatch(), so existing dispatchers keep
     *             working; it will be removed in the next major version.
     * @see self::dispatch()
     *
     * @param array<string,string|ValueInterface> $data
     *
     * @throws DigitalMarketingFrameworkException
     */
    public function send(array $data): void;

    /**
     * Describes what send() would do, without doing it.
     *
     * @deprecated Override previewDispatch() instead. See send().
     * @see self::previewDispatch()
     *
     * @param array<string,string|ValueInterface> $data
     *
     * @return array<string,mixed>
     *
     * @throws DigitalMarketingFrameworkException
     */
    public function preview(array $data): array;
}
