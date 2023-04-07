<?php

namespace Tests\Feature;

use Brightfish\CachingGuzzle\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\FeatureTestCase;

class ClientFeatureTest extends FeatureTestCase
{
    /** @var string Mock */
    public const TEST_URL = 'https://brave.com/';

    /** @var string */
    public const TEST_STR = 'I\'m a lion!';

    public function testCaching()
    {
        $response = new Response(200, [], static::TEST_STR);
        $mock = new MockHandler([$response]);

        $stack = HandlerStack::create($mock);

        $client = new Client($this->store, [
            'cache_ttl' => 12345,
            'cache_log' => false,
            'handler' => $stack,
        ]);

        # This response will be cached
        $client->get(static::TEST_URL, [
            'cache_ttl' => 3600,
        ]);

        $key = $this->getCacheKeyFromUrl(static::TEST_URL);

        $cached = $this->store->get($key);

        $this->assertStringContainsString(static::TEST_STR, $cached);

        // echo PHP_EOL . 'ClientFeatureTest response: ' . $cached . PHP_EOL;
    }
}
