<?php

namespace Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Brightfish\CachingGuzzle\Middleware;
use GuzzleHttp\Psr7\Response;
use Tests\FeatureTestCase;

class MiddlewareFeatureTest extends FeatureTestCase
{
    /** @var string Mock */
    const TEST_URL = 'https://duckduckgo.com/';

    /** @var string */
    const TEST_STR = 'I\'m a duck!';

    public function testCaching()
    {
        $handler = new Middleware($this->store, 3600);

        $response = new Response(200, [], static::TEST_STR);
        $mock = new MockHandler([$response]);

        $stack = HandlerStack::create($mock);
        $stack->push($handler);

        $client = new Client([
            'handler' => $stack
        ]);

        $client->get(static::TEST_URL);

        $key = $this->getCacheKeyFromUrl(static::TEST_URL);

        $cached = $this->store->get($key);

        $this->assertStringContainsString(static::TEST_STR, $cached);

        // echo PHP_EOL . 'MiddlewareFeatureTest response: ' . $cached . PHP_EOL;
    }
}
