<?php

namespace DigitalMarketingFramework\Distributor\Core\Api\EndPoint;

use DigitalMarketingFramework\Distributor\Core\Api\EndPoint\EndPointStorageInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Api\EndPointInterface;

class EndPointStorage implements EndPointStorageInterface
{
    protected array $endpoints = [];

    public function getEndPointFromSegment(string $segment): ?EndPointInterface
    {
        return $this->endpoints[$segment] ?? null;
    }

    public function getAllEndPoints(): array
    {
        return $this->endpoints;
    }

    public function addEndPoint(EndPointInterface $endPoint): void
    {
        $this->endpoints[$endPoint->getPathSegment()] = $endPoint;
    }

    public function removeEndPoint(EndPointInterface $endPoint): void
    {
        unset($this->endpoints[$endPoint->getPathSegment()]);
    }

    public function updateEndPoint(EndPointInterface $endPoint): void
    {
        $this->endpoints[$endPoint->getPathSegment()] = $endPoint;
    }
}
