<?php

namespace DigitalMarketingFramework\Distributor\Core\Api\EndPoint;

use DigitalMarketingFramework\Distributor\Core\Model\Api\EndPointInterface;

interface EndPointStorageInterface
{
    public function getEndPointByName(string $name): ?EndPointInterface;

    /**
     * @return array<EndPointInterface>
     */
    public function getAllEndPoints(): array;

    public function addEndPoint(EndPointInterface $endPoint): void;

    public function removeEndPoint(EndPointInterface $endPoint): void;

    public function updateEndPoint(EndPointInterface $endPoint): void;
}
