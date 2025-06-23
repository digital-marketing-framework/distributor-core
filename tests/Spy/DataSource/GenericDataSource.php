<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Spy\DataSource;

use DigitalMarketingFramework\Distributor\Core\Model\DataSource\DistributorDataSource;

class GenericDataSource extends DistributorDataSource
{
    public function __construct(
        string $configurationDocument,
        string $hash = '',
        string $name = 'generic',
    ) {
        if ($hash === '') {
            $hash = md5($name);
        }

        $identifier = 'generic:' . $name;

        parent::__construct(
            'generic',
            $identifier,
            $name,
            $hash,
            $configurationDocument
        );
    }
}
