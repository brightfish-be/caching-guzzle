<?php

namespace Brightfish\CachingGuzzle;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Guzzle middleware, caches Guzzle HTTP responses.
 *
 * @copyleft 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class Middleware
{
    /**
     * PSR-16 Cache interface implementation.
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Cache time to live.
     * @var int
     */
    protected $ttl = 0;

    /**
     * Log the cache requests.
     * @var bool
     */
    protected $track = false;

    /**
     * Cache the laravel cache driver instance.
     * @param CacheInterface $cache             Cache handler implementation
     * @param int $ttl                          Time to live in minutes
     * @param bool $log                         Whether to log the cache requests
     */
    public function __construct(CacheInterface $cache, int $ttl = 0, bool $log = false)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
        $this->track = $log;
    }

    /**
     * The middleware handler.
     * @param callable $handler
     * @return callable
     */
    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use (&$handler) {
            # If the request allows caching, create a key to fetch/store the response
            $cacheKey = ($options['cache'] ?? true) ? $this->makeKey($request->getUri()) : '';

            # Get from cache if cached
            if ($cacheKey && $entry = $this->get($cacheKey)) {
                return $entry;
            }

            /** @var PromiseInterface $promise */
            $promise = $handler($request, $options);

            return $promise->then(
                function (ResponseInterface $response) use ($options, $cacheKey) {
                    if ($cacheKey) {
                        $this->save($cacheKey, $response, $options['cache_ttl'] ?? $this->ttl);
                    }

                    return $response;
                }
            );
        };
    }

    /**
     * Create the key which will reference the cache entry.
     * @param UriInterface $uri
     * @return string
     */
    protected function makeKey(UriInterface $uri)
    {
        return (string)preg_replace('#(https?:)#', '', (string)$uri);
    }

    /**
     * Cache the data.
     * @param string $key
     * @return FulfilledPromise|null
     * @throws InvalidArgumentException
     */
    protected function get(string $key): ?FulfilledPromise
    {
        $entry = $this->cache->get($key);

        if (is_null($entry)) {
            return $entry;
        }

        if ($this->track) {
            $this->log($key);
        }

        return new FulfilledPromise(
            new Response(200, [], $entry)
        );
    }

    /**
     * Persist the data.
     * @param string $key
     * @param ResponseInterface|null $response
     * @param int $ttl
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function save(string $key, ?ResponseInterface $response, int $ttl): bool
    {
        if ($response && $response->getStatusCode() === 200) {
            $saved = $this->cache->set($key, (string)$response->getBody(), $ttl) ?? true;

            if ($response->getBody()->isSeekable()) {
                $response->getBody()->rewind();
            }

            return $saved;
        }

        return false;
    }

    /**
     * Track the cache request to a log file.
     * @param string $cacheKey
     * @return mixed
     */
    protected function log(string $cacheKey)
    {
        $msg = "Retrieved from cache: $cacheKey";

        # Laravel support
        if (function_exists('logger')) {
            return logger($msg);
        }

        return error_log($msg);
    }
}
