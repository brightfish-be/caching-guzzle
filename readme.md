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

## Using the middleware (with Laravel)
```
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Brightfish\CachingGuzzle\Middleware;

$store = app('cache')->store('database');
$handler = new Middleware($store, 3600);
$stack = HandlerStack::create();
$stack->push($handler);
$client = new Client(['handler' => $stack]);
```

## Making requests and retrieving from cache
```
# This response will be cached for 60s
$response_1 = $client->get('/resource', [
    'cache_ttl' => 60
]);

# This response will not be cached
$response_2 = $client->post('/resource/84', [
    'cache' => false
]);

# This response will be cached with a custom key
$response_3 = $client->post('/resource/84', [
    'cache_key' => 'my-key'
]);

# Get response_1 from cache
$cached_response_1 = $store->get('//example.org/api/resource');

# Get response_3 from cache
$cached_response_3 = $store->get('my-key');
```

## Using the wrapper
Instead of manually configuring the Guzzle client and the caching middleware, it is also possible
to instantiate the Client class provided in this package. This way, the binding of the middleware is done for you.
```
use Brightfish\CachingGuzzle\Client;

$client = new Client($psrCompatibleCache, [
    'cache_ttl' => 12345,
    'cache_log' => false,
    'base_uri' => 'https://example.org/api'
]);
```

## Available options

### Per request:

- `$cache (bool)` Whether to disable the cache for this specific request
- `$cache_ttl (int)` Specific time to live in minutes for this request
- `$cache_key (string)` Custom cache key to override the default request URI key

```
$response_1 = $client->get('/resource', [
    'cache' => false,
    'cache_ttl' => 3600
]);
```

### When instantiating the wrapper, options can be passed along with the Guzzle options:  

- `$cache (\Psr\SimpleCache\CacheInterface)` Cache handler implementation
- `$cache_ttl (int)` Default time to live in minutes
- `$cache_log (bool)` Whether to log the cache requests (in Laravel)  

```
$client = new Client($cache, [
    'cache_ttl' => 12345,
    'cache_log' => app()->environment('local'),
    'base_uri' => 'https://example.org/api'
]);
```

### When instantiating the middleware:  

- `$cache (\Psr\SimpleCache\CacheInterface)` Cache handler implementation
- `$ttl (int)` Default time to live in minutes
- `$log (bool)` Whether to log the cache requests (in Laravel)  

```
$handler = new CacheMiddleware($cache, $ttl, $log);
```

## License
GNU General Public License (GPL). Please see the license file for more information.
