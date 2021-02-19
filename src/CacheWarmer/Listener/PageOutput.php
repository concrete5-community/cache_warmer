<?php

namespace A3020\CacheWarmer\Listener;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Logging\Logger;
use Exception;

class PageOutput
{
    /**
     * @var Repository
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Repository $config, Logger $logger)
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
            $this->logger->addDebug($e->getMessage());
        }
    }
}
