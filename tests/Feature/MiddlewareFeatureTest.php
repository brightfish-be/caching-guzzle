<?php

namespace Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Brightfish\CachingGuzzle\Middleware;
use GuzzleHttp\Psr7\Response;
use Tests\FeatureTestCase;
use function GuzzleHttp\Promise\settle;
use function GuzzleHttp\Promise\unwrap;

class MiddlewareFeatureTest extends FeatureTestCase
{
    /** @var string Mock */
    const TEST_URL = 'https://duckduckgo.com/';

    /** @var string */
    const TEST_STR = 'I\'m a duck!';

    public function test_caching()
    {
        $middleware = new Middleware($this->store, 3600);

        $mock = new MockHandler([
            new Response(200, [], static::TEST_STR),
            new Response(200, [], static::TEST_STR)
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push($middleware);

        $client = new Client([
            'handler' => $stack
        ]);

        $client->get(static::TEST_URL);

        $key = $this->getCacheKeyFromUrl(static::TEST_URL);

        $cached = $this->store->get($key);

        $this->assertStringContainsString(static::TEST_STR, $cached);

        # Request with custom key

        $client->get(static::TEST_URL, ['cache_key' => 'test_key']);

        $cached = $this->store->get('test_key');

        $this->assertStringContainsString(static::TEST_STR, $cached);

        // echo PHP_EOL . 'MiddlewareFeatureTest response: ' . $cached . PHP_EOL;
    }

    public function test_caching_with_promised_request()
    {
        $middleware = new Middleware($this->store, 3600);

        $mock = new MockHandler([
            new Response(200, [], static::TEST_STR),
            new Response(200, [], static::TEST_STR)
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push($middleware);

        $client = new Client([
            'handler' => $stack
        ]);

        $promises = [
            'prom_1' => $client->getAsync(static::TEST_URL),
        ];

        $responses = unwrap($promises);

        $this->assertEquals(
            (string)$responses['prom_1']->getBody(),
            static::TEST_STR
        );
    }
}
