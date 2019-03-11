<?php namespace Brightfish\CachingGuzzle;

use GuzzleHttp\HandlerStack;
use Psr\SimpleCache\CacheInterface;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Guzzle client wrapper, instantiates the middleware
 *
 * @package CachingGuzzle
 * @copyleft 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class Client extends GuzzleClient
{
    /**
     * Create a middleware stack and instantiate Guzzle
     * @inheritdoc
     * @param array $config
     */
    public function __construct(CacheInterface $cache, array $config = [])
    {
        if (empty($config['handler'])) {
            $config['handler'] = HandlerStack::create();
        }

        $ttl = $config['cache_ttl'] ?? 3600;
        $log = $config['cache_log'] ?? false;

        $config['handler']->push(new Middleware($cache, $ttl, $log));

        unset($config['cache'], $config['cache_ttl'], $config['cache_log']);

        parent::__construct($config);
    }
}
