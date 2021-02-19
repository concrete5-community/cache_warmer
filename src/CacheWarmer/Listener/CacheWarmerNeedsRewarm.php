<?php

namespace A3020\CacheWarmer\Listener;

class CacheWarmerNeedsRewarm extends CacheFlush
{
    /*
     * Custom code may trigger the on_cache_warmer_needs_rewarm event.
     * The behavior of this event is the same as the CacheFlush class,
     * that's why it's simply extending that class.
     */
}
