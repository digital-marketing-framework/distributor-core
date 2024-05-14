<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\Api;

interface EndPointInterface
{
    public function getPathSegment(): string;

    public function setPathSegment(string $pathSegment): void;

    public function getConfigurationDocument(): string;

    public function setConfigurationDocument(string $configurationDocument): void;
}
