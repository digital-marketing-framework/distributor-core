<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\Api;

class EndPoint implements EndPointInterface
{
    public function __construct(
        protected string $pathSegment,
        protected string $configurationDocument,
    ) {
    }

    public function getPathSegment(): string
    {
        return $this->pathSegment;
    }

    public function setPathSegment(string $pathSegment): void
    {
        $this->pathSegment = $pathSegment;
    }

    public function getConfigurationDocument(): string
    {
        return $this->configurationDocument;
    }

    public function setConfigurationDocument(string $configurationDocument): void
    {
        $this->configurationDocument = $configurationDocument;
    }
}
