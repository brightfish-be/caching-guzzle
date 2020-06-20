<?php

namespace Tests;

use Brightfish\CachingGuzzle\Middleware;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Tests\Mocks\Cache;

class FeatureTestCase extends TestCase
{
    /** @var Cache */
    protected $store;

    public function setUp(): void
    {
        parent::setUp();

        $this->store = new Cache();
    }

    protected function getCacheKeyFromUrl(string $url): string
    {
        $parts = explode(':', $url);

        return array_pop($parts);
    }

    protected function getClientWithMockResponses(array $responses)
    {
        $middleware = new Middleware($this->store, 3600);

        $responses = array_map(function (string $str) {
            return new Response(200, [], $str);
        }, $responses);

        $mock = new MockHandler($responses);

        $stack = HandlerStack::create($mock);
        $stack->push($middleware);

        return new Client([
            'handler' => $stack,
        ]);
    }
}
