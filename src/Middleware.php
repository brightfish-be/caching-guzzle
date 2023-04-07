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
     * Duration to live in cache, default to 1 min.
     * @var int Seconds
     */
    protected $ttl = 60;

    /**
     * Log the cache requests.
     * @var bool
     */
    protected $track = false;

    /**
     * Cache the laravel cache driver instance.
     * @param CacheInterface $cache Cache handler implementation
     * @param int $ttl Default cache duration in seconds
     * @param bool $log Whether to log the cache requests
     */
    public function __construct(CacheInterface $cache, int $ttl = 60, bool $log = false)
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
            # By default caching is allowed, else return early
            if (! ($options['cache'] ?? true)) {
                return $handler($request, $options);
            }

            # Create the cache key
            $key = $this->getKey($request->getUri(), $options['cache_key'] ?? '');

            # Should we bypass current cached value?
            $bypass = $options['cache_anew'] ?? false;

            # Try to get from cache
            if (! $bypass && $entry = $this->get($key)) {
                return $entry;
            }

            /** @var PromiseInterface $promise */
            $promise = $handler($request, $options);

            return $promise->then(
                function (ResponseInterface $response) use ($options, $key) {
                    if ($ttl = $this->getTTL($options)) {
                        $this->save($key, $response, $ttl);
                    }

                    return $response;
                }
            );
        };
    }

    /**
     * Either return the custom passed key or the request URL minus the protocol.
     * @param UriInterface $uri
     * @param string $key
     * @return string
     */
    protected function getKey(UriInterface $uri, string $key = ''): string
    {
        return $key ?: preg_replace('#(https?:)#', '', (string)$uri);
    }

    /**
     * Get duration the data should stay in cache.
     * @param array $options
     * @return int $seconds
     */
    protected function getTTL(array $options): int
    {
        $duration = $options['cache_ttl'] ?? $this->ttl;

        $duration = $duration !== -1 ? $duration : 631152000;

        return $duration;
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
