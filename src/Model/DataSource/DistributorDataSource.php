<?php

namespace DigitalMarketingFramework\Distributor\Core\Model\DataSource;

use DigitalMarketingFramework\Core\Model\DataSource\DataSource;

abstract class DistributorDataSource extends DataSource implements DistributorDataSourceInterface
{
    public function __construct(
        string $type,
        string $identifier,
        string $name,
        string $hash,
        string $configurationDocument,
    ) {
        parent::__construct('distributor', $type, $identifier, $name, $hash, $configurationDocument);
    }
}
