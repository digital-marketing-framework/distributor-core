<?php

namespace DigitalMarketingFramework\Distributor\Core\Tests\Route;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Integration\IntegrationInfo;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRoute;

class GenericRoute extends OutboundRoute
{
    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        protected SubmissionDataSetInterface $submission,
        protected string $routeId,
        protected ?DataDispatcherInterface $dataDispatcher = null,
    ) {
        parent::__construct($keyword, $registry, $submission, $routeId);
    }

    public static function getDefaultIntegrationInfo(): IntegrationInfo
    {
        return new IntegrationInfo('generic');
    }

    protected function getDispatcher(): DataDispatcherInterface
    {
        if (!$this->dataDispatcher instanceof DataDispatcherInterface) {
            throw new DigitalMarketingFrameworkException('generic route has no data dispatcher');
        }

        return $this->dataDispatcher;
    }
}
