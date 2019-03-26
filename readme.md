# Guzzle caching

Simple caching middleware for Guzzle, works well with Laravel or with any cache system 
implementing the PSR-16 caching interface.  

## Installation
```
composer require brightfish/caching-guzzle
```

## Using the wrapper (with Laravel)
```
use Brightfish\CachingGuzzle\Client;

/** @var \Psr\SimpleCache\CacheInterface $store */
$store = app('cache')->store('database');

$client = new Client($store, [
    'cache_ttl' => 12345,
    'cache_log' => app()->environment('local')
    'base_uri' => 'https://example.org/api'
]);

# This response will be cached
$response_1 = $client->get('/resource', [
    'cache_ttl' => 3600
]);

# This response will not be cached
$response_2 = $client->post('/resource/84', [
    'cache' => false
]);

# Get response_1 from cache
$cached_response = $store->get('//example.org/api/resource');
```

## Using the middleware (with Laravel)
```
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Brightfish\CachingGuzzle\Middleware;

$store = app('cache')->store('database');
$handler = new CacheMiddleware($store, 3600);
$stack = HandlerStack::create();
$stack->push($handler);
$client = new Client(['handler' => $stack]);
```

## Available options

### Per request:

- `$cache (bool)` Whether to disable the cache for this specific request
- `$cache_ttl (int)` Specific time to live in minutes for this request

```
$response_1 = $client->get('/resource', [
    'cache' => false
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
    'cache_log' => app()->environment('local')
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
GNU General Public License (GPL). Please see License File for more information.

