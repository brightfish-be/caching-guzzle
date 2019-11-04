<?php

namespace Tests;

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
}