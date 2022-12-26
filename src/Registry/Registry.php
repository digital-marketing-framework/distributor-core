<?php

namespace DigitalMarketingFramework\Distributor\Core\Registry;

use DigitalMarketingFramework\Core\Cache\DataCache;
use DigitalMarketingFramework\Core\Cache\DataCacheInterface;
use DigitalMarketingFramework\Core\Cache\NonPersistentCache;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\RequestContext;
use DigitalMarketingFramework\Core\Log\LoggerFactoryInterface;
use DigitalMarketingFramework\Core\Log\NullLoggerFactory;
use DigitalMarketingFramework\Core\Model\Configuration\Configuration;
use DigitalMarketingFramework\Core\Model\Configuration\ConfigurationInterface;
use DigitalMarketingFramework\Core\Queue\NonPersistentQueue;
use DigitalMarketingFramework\Core\Queue\QueueInterface;
use DigitalMarketingFramework\Core\Registry\Registry as CoreRegistry;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactory;
use DigitalMarketingFramework\Distributor\Core\Factory\QueueDataFactoryInterface;

class Registry extends CoreRegistry implements RegistryInterface
{
    use RegistryTrait;

    public function __construct(
        LoggerFactoryInterface $loggerFactory = new NullLoggerFactory(),
        ContextInterface $context = new RequestContext(),
        DataCacheInterface $cache = new DataCache(new NonPersistentCache()),
        ConfigurationInterface $globalConfiguration = new Configuration([]),
        protected QueueInterface $persistentQueue = new NonPersistentQueue(),
        protected QueueInterface $nonPersistentQueue = new NonPersistentQueue(),
        protected QueueDataFactoryInterface $queueDataFactory = new QueueDataFactory(),
    ) {
        parent::__construct($loggerFactory, $context, $cache, $globalConfiguration);
    }
}
