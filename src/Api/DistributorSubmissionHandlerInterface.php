<?php

namespace DigitalMarketingFramework\Distributor\Core\Api;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Model\Api\EndPointInterface;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;

interface DistributorSubmissionHandlerInterface
{
    /**
     * @param array<string,mixed> $dataSourceContext
     * @param array<string,mixed>|DistributorConfigurationInterface $configuration
     * @param array<string,string|ValueInterface>|DataInterface $data
     * @param array<string,mixed>|ContextInterface|null $context
     */
    public function submit(
        string $dataSourceId,
        array $dataSourceContext,
        array|DistributorConfigurationInterface $configuration,
        array|DataInterface $data,
        array|ContextInterface|null $context = null,
    ): void;

    /**
     * @param array<string,string|ValueInterface>|DataInterface $data
     * @param array<string,mixed>|ContextInterface|null $context
     */
    public function submitToEndPoint(
        EndPointInterface $endPoint,
        array|DataInterface $data,
        array|ContextInterface|null $context = null,
    ): void;

    /**
     * @return array<string>
     */
    public function getEndPointNames(bool $frontend = false): array;
}
