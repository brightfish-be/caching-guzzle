# HTTP caching middleware for Guzzle

[![Tests](https://github.com/brightfish-be/caching-guzzle/workflows/Tests/badge.svg?event=push&style=flat-square)](https://github.com/brightfish-be/caching-guzzle/actions)
[![GitHub release (latest by date)](https://img.shields.io/github/v/release/brightfish-be/caching-guzzle?color=blue&label=Latest%20version&style=flat-square)](https://github.com/brightfish-be/caching-guzzle/releases)
[![Packagist](https://img.shields.io/packagist/dt/brightfish/caching-guzzle?label=Downloads&style=flat-square)](https://packagist.org/packages/brightfish/caching-guzzle)

Simple and transparent HTTP response caching middleware for [Guzzle 6](https://github.com/guzzle/guzzle/), 
works well with [Laravel](https://github.com/laravel) or with any caching library 
implementing the [PSR-16 caching interface](https://www.php-fig.org/psr/psr-16/).  

## How to use
The registration of the middleware follows [Guzzle's documentation](http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#handlers):

```php
/** @var \Psr\SimpleCache\CacheInterface $store */
$cache = app('cache')->store('database'); // Laravel example, but any PSR-16 cache will do

$middleware = new \Brightfish\CachingGuzzle\Middleware($cache);

$stack = \GuzzleHttp\HandlerStack::create();

$stack->push($middleware);

$client = new \GuzzleHttp\Client([
    'handler' => $stack,
    'base_uri' => 'https://example.org/api/'
]);
```

### Instantiation parameters
Next to a PSR-16 compliant cache, the middleware takes two optional parameters: 
- `$ttl`, the default cache duration, which can be overridden by each request
- `$log`, instructs the package to log cache hits Laravel's log or PHP's default `error_log` (see [source](https://github.com/brightfish-be/caching-guzzle/blob/c0e96ae157b4e17363eb76ee5996995fbf0bd4a5/src/Middleware.php#L168)).

```php
$middleware = new \Brightfish\CachingGuzzle\Middleware($store, $ttl = 60, $log = true);
```

## Making requests

**Available options:**   

| Option | Type | Default | Description |
|:-------|------|---------|:------------|
|`cache` | bool | `true` | Completely disable the cache for this request |
|`cache_anew` | bool | `false` | Bypass the cache and replace it with the new response |
|`cache_ttl` | int | `60` | Cache duration in seconds, use `-1` to cache forever |
|`cache_key` | string | `true` | Cache key to override the default one based on the request URI (see [Cache retrieval](https://github.com/brightfish-be/caching-guzzle#cache-retrieval)) |

### Example: cache the response for 90s (default: 60)
```php
$response_1 = $client->get('resource', [
    'cache_ttl' => 90
]);
```
### Example: request anew and update the cache
```php
$response_3 = $client->post('resource/84', [
    'cache_anew' => true
]);
```
### Example: disable caching
```php
$response_2 = $client->post('resource/84', [
    'cache' => false
]);
```
### Example: cache forever with a custom key
```php
$response_4 = $client->post('resource/84', [
    'cache_key' => 'my-key',
    'cache_ttl' => -1
]);
```
If `cache_ttl` is set to `0` the response will not be cached, but, contrary to `'cache' => false`, it may be retrieved from it.

## Example: cache retrieval
```php
# Get response_1 from cache.
$cached_response_1 = $store->get('//example.org/api/resource');

# Get response_4 from cache.
$cached_response_4 = $store->get('my-key');
```

## Using the wrapper
Instead of manually configuring Guzzle's client and the caching middleware, it is also possible to instantiate the `Client` class provided by this package. This way, the binding of the middleware is done for you.

```php
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

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [dotburo](https://github.com/dotburo)
- [Peter Forret](https://github.com/pforret)

## License

GNU General Public License (GPL). Please see the [license file](LICENSE.md) for more information.
