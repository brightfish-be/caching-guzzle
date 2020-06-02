<?php

namespace Brightfish\CachingGuzzle;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use Psr\SimpleCache\CacheInterface;

/**
 * Guzzle client wrapper, instantiates the middleware.
 *
 * @copyleft 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class Client extends GuzzleClient
{
    /**
     * Create a middleware stack and instantiate Guzzle.
     * {@inheritDoc}
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache, array $config = [])
    {
        if (empty($config['handler'])) {
            $config['handler'] = HandlerStack::create();
        }

        $ttl = $config['cache_ttl'] ?? 60;
        $log = $config['cache_log'] ?? false;

        $config['handler']->push(new Middleware($cache, $ttl, $log));

        unset(
            $config['cache'],
            $config['cache_anew'],
            $config['cache_ttl'],
            $config['cache_log'],
            $config['cache_key']
        );

        parent::__construct($config);
    }
}
