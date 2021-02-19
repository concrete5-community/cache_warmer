<?php

namespace A3020\CacheWarmer\Listener;

use Exception;
use Concrete\Core\Config\Repository\Repository;
use Psr\Log\LoggerInterface;

class CacheFlush
{
    /**
     * @var \Concrete\Core\Config\Repository\Repository
     */
    private $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(Repository $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param \Symfony\Component\EventDispatcher\GenericEvent $event
     */
    public function handle($event)
    {
        try {
            // The cache has been flushed. Let's write to the config
            // that the cache needs to 'warmed up' again.
            // Next time the CLI job runs via CLI, it'll check this value.
            $this->config->save('cache_warmer.settings.needs_rewarm', true);
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
