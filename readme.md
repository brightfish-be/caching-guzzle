# HTTP cache middleware for Guzzle

[![GitHub release (latest by date)](https://img.shields.io/github/v/release/brightfish-be/caching-guzzle?color=blue&label=Latest%20version&style=flat-square)](https://github.com/brightfish-be/caching-guzzle/releases)
[![Travis CI](https://travis-ci.com/brightfish-be/caching-guzzle.svg?branch=master&label=Build&style=flat-square)](https://travis-ci.com/brightfish-be/caching-guzzle)
[![StyleCI](https://styleci.io/repos/175029173/shield)](https://styleci.io/repos/175029173)
[![Packagist](https://img.shields.io/packagist/dt/brightfish/caching-guzzle?label=Total%20downloads&style=flat-square)](https://packagist.org/packages/brightfish/caching-guzzle)

Simple caching middleware for Guzzle, works well with Laravel or with any cache system 
implementing the PSR-16 caching interface.  

## Installation
```
composer require brightfish/caching-guzzle
```

## Using the middleware
```
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Brightfish\CachingGuzzle\Middleware;

$store = app('cache')->store('database'); // Laravel
$handler = new Middleware($store, 3600);
$stack = HandlerStack::create();
$stack->push($handler);
$client = new Client([
    'handler' => $stack,
    'base_uri' => 'https://example.org/api/'
]);
```

## Making requests and retrieving from cache
```
# This response will be cached for 60s (same as default).
$response_1 = $client->get('resource', [
    'cache_ttl' => 60
]);

# This response will not be cached.
$response_2 = $client->post('resource/84', [
    'cache' => false
]);

# This response will be cached forever with a custom key.
$response_3 = $client->post('resource/84', [
    'cache_key' => 'my-key',
    'cache_ttl' => -1
]);

# Get response_1 from cache.
$cached_response_1 = $store->get('//example.org/api/resource');

# Get response_3 from cache.
$cached_response_3 = $store->get('my-key');
```

## Using the wrapper
Instead of manually configuring the Guzzle client and the caching middleware, it is also possible
to instantiate the Client class provided in this package. This way, the binding of the middleware is done for you.
```
use Brightfish\CachingGuzzle\Client;

$client = new Client($psrCompatibleCache, [
    'cache_ttl' => 3600,
    'cache_log' => false,
    'base_uri' => 'https://example.org/api/'
]);
```

## Available options

### Per request

- `$cache` (bool): whether to disable the cache for this specific request.
- `$cache_ttl` (int): cache duration in seconds for this response, use `-1` to cache forever.
- `$cache_key` (string): custom cache key to override the default one based on the request URI.

```
$response_1 = $client->get('resource', [
    'cache' => false,
    'cache_ttl' => 3600
]);
```

### When instantiating the middleware

- `$cache` (\Psr\SimpleCache\CacheInterface): cache handler implementation.
- `$ttl` (int): default cache duration in seconds [default: 60].
- `$log` (bool): whether to log the cache requests [default: false].  

```
$handler = new CacheMiddleware($cache, $ttl, $log);
```

### When instantiating the wrapper, pass options along with the Guzzle ones

- `$cache` (\Psr\SimpleCache\CacheInterface): cache handler implementation.
- `$cache_ttl` (int): default cache duration in seconds [default: 60].
- `$cache_log` (bool): whether to log the cache requests [default: false].

```
$client = new Client($cache, [
    'cache_ttl' => 12345,
    'cache_log' => true,
    'base_uri' => 'https://example.org/api/'
]);
```

## License
GNU General Public License (GPL). Please see the license file for more information.
