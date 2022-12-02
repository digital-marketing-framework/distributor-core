<?php

namespace DigitalMarketingFramework\Distributer\Core\Registry;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\RequestContext;
use DigitalMarketingFramework\Core\Log\LoggerFactoryInterface;
use DigitalMarketingFramework\Core\Log\NullLoggerFactory;
use DigitalMarketingFramework\Core\Queue\NonPersistentQueue;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Registry\Registry as CoreRegistry;
use DigitalMarketingFramework\Distributer\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributer\Core\Factory\QueueDataFactoryInterface;

class Registry extends CoreRegistry implements RegistryInterface
{
    use RegistryTrait;

    public function __construct(
        protected LoggerFactoryInterface $loggerFactory = new NullLoggerFactory(),
        protected ContextInterface $context = new RequestContext(),
        protected QueueInterface $persistentQueue = new NonPersistentQueue(),
        protected QueueInterface $nonPersistentQueue = new NonPersistentQueue(),
        protected QueueDataFactoryInterface $queueDataFactory = new QueueDataFactory(),
    ) {
    }
}
