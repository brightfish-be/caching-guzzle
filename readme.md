# HTTP cache middleware for Guzzle

[![GitHub release (latest by date)](https://img.shields.io/github/v/release/brightfish-be/caching-guzzle?color=blue&label=Latest%20version&style=flat-square)](https://github.com/brightfish-be/caching-guzzle/releases)
[![Travis CI](https://travis-ci.com/brightfish-be/caching-guzzle.svg?branch=master&label=Build&style=flat-square)](https://travis-ci.com/brightfish-be/caching-guzzle)
[![StyleCI](https://styleci.io/repos/175029173/shield)](https://styleci.io/repos/175029173)
[![Packagist](https://img.shields.io/packagist/dt/brightfish/caching-guzzle?label=Total%20downloads&style=flat-square)](https://packagist.org/packages/brightfish/caching-guzzle)

Simple caching middleware for [Guzzle](https://github.com/guzzle/guzzle/), works well with [Laravel](https://github.com/laravel) or with any cache system 
implementing the [PSR-16 caching interface](https://www.php-fig.org/psr/psr-16/).  

## Installation
```
composer require brightfish/caching-guzzle
```

## Usage
The registration of the caching middleware follows [Guzzle's documentation](http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#handlers).

```
/** @var \Psr\SimpleCache\CacheInterface $store */
$cache = app('cache')->store('database'); // Laravel example

$middleware = new \Brightfish\CachingGuzzle\Middleware($cache);

$stack = \GuzzleHttp\HandlerStack::create();

$stack->push($middleware);

$client = new \GuzzleHttp\Client([
    'handler' => $stack,
    'base_uri' => 'https://example.org/api/'
]);
```

### Instantiation parameters
Instantiating the middleware takes 3 parameters: `new Middleware($store, $ttl = 60, $log = true)`, where only `$store`, a `SimpleCache` implementation is required. `$ttl` is the default cache duration, which can be overridden by each request. And finally, if `$log` is true, cache hits will be written to Laravel's log or the `error_log` defined by PHP (see [source](https://github.com/brightfish-be/caching-guzzle/blob/c0e96ae157b4e17363eb76ee5996995fbf0bd4a5/src/Middleware.php#L168)).


## Making requests

**Available options:**   

| Option | Type | Default | Description |
|:-------|------|---------|:------------|
|`cache` | bool | `true` | Completely disable the cache for this request |
|`cache_anew` | bool | `false` | Bypass the cache and replace it with the new response |
|`cache_ttl` | int | `60` | Cache duration in seconds, use `-1` to cache forever |
|`cache_key` | string | `true` | Cache key to override the default one based on the request URI (see [Cache retrieval](https://github.com/brightfish-be/caching-guzzle#cache-retrieval)) |

### Cache the response for 60s (default)
```
$response_1 = $client->get('resource', [
    'cache_ttl' => 60
]);
```
### Request anew and update the cache
```
$response_3 = $client->post('resource/84', [
    'cache_anew' => true
]);
```
### Disable caching
```
$response_2 = $client->post('resource/84', [
    'cache' => false
]);
```
### Cache forever with a custom key
```
$response_4 = $client->post('resource/84', [
    'cache_key' => 'my-key',
    'cache_ttl' => -1
]);
```
If `cache_ttl` is set to `0` the response will not be cached, but, contrary to `'cache' => false`, it may be retrieved from it.

## Cache retrieval
```
# Get response_1 from cache.
$cached_response_1 = $store->get('//example.org/api/resource');

# Get response_4 from cache.
$cached_response_4 = $store->get('my-key');
```

## Using the wrapper
Instead of manually configuring Guzzle's client and the caching middleware, it is also possible to instantiate the `Client` class provided by this package. This way, the binding of the middleware is done for you.

```
use Brightfish\CachingGuzzle\Client;

/** @var \Psr\SimpleCache\CacheInterface $store */
$psrCompatibleCache = new Cache();

$client = new Client($psrCompatibleCache, [
    'cache_ttl' => 60,	   // default cache duration
    'cache_log' => false,  // log the cache hits
    // Guzzle options:
    'base_uri' => 'https://example.org/api/'
    // ...
]);
```

## License
GNU General Public License (GPL). Please see the license file for more information.
