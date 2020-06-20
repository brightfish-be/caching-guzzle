<?php

namespace Tests\Feature;

use function GuzzleHttp\Promise\unwrap;
use Tests\FeatureTestCase;

class MiddlewareFeatureTest extends FeatureTestCase
{
    /** @var string Mock */
    const TEST_URL = 'https://duckduckgo.com/';

    /** @var string */
    const TEST_STR_1 = 'I\'m a duck!';

    const TEST_STR_2 = 'I\'m a duck too!';

    public function test_caching()
    {
        $client = $this->getClientWithMockResponses([
            static::TEST_STR_1,
        ]);

        $client->get(static::TEST_URL);

        $key = $this->getCacheKeyFromUrl(static::TEST_URL);

        $cached = $this->store->get($key);

        $this->assertStringContainsString(static::TEST_STR_1, $cached);
    }

    public function test_caching_with_custom_key()
    {
        $client = $this->getClientWithMockResponses([
            static::TEST_STR_2,
        ]);

        $client->get(static::TEST_URL, ['cache_key' => 'test_key']);

        $cached = $this->store->get('test_key');

        $this->assertStringContainsString(static::TEST_STR_2, $cached);
    }

    public function test_caching_anew()
    {
        $client = $this->getClientWithMockResponses([
            static::TEST_STR_1,
            static::TEST_STR_2,
        ]);

        $client->get(static::TEST_URL);

        $client->get(static::TEST_URL, [
            'cache_anew' => true,
            'cache_key' => 'test_key',
        ]);

        $cached = $this->store->get('test_key');

        $this->assertStringContainsString(static::TEST_STR_2, $cached);
    }

    public function test_caching_with_promised_request()
    {
        $client = $this->getClientWithMockResponses([
            static::TEST_STR_1,
        ]);

        $promises = [
            'prom_1' => $client->getAsync(static::TEST_URL),
        ];

        $responses = unwrap($promises);

        $this->assertEquals(
            (string)$responses['prom_1']->getBody(),
            static::TEST_STR_1
        );
    }
}
