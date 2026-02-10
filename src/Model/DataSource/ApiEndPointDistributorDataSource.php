<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\DataSource;

use DigitalMarketingFramework\Core\Model\Api\EndPointInterface;

class ApiEndPointDistributorDataSource extends DistributorDataSource
{
    public const TYPE = 'api';

    public function __construct(
        protected EndPointInterface $endPoint,
    ) {
        $hashData = [
            'name' => $this->endPoint->getName(),
            'enabled' => $this->endPoint->getEnabled(),
            'pushEnabled' => $this->endPoint->getPushEnabled(),
            'pullEnabled' => $this->endPoint->getPullEnabled(),
            'disableContext' => $this->endPoint->getDisableContext(),
            'allowContextOverride' => $this->endPoint->getAllowContextOverride(),
            'exposeToFrondEnd' => $this->endPoint->getExposeToFrontend(),
            'configurationDocument' => $this->endPoint->getConfigurationDocument(),
        ];
        $hash = md5(json_encode($hashData));

        parent::__construct(
            static::TYPE,
            static::TYPE . ':' . $endPoint->getName(),
            $hash,
            $endPoint->getName(),
            $endPoint->getConfigurationDocument()
        );
    }

    public function getEndPoint(): EndPointInterface
    {
        return $this->endPoint;
    }
}
