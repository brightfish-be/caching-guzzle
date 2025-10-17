<?php

namespace Tests\Mocks;

use Psr\SimpleCache\CacheInterface;

/**
 * Array cache as testing mock
 *
 * @copyright 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolssaet@brightfish.be>
 */
class Cache implements CacheInterface
{
    /**
     * @var array
     */
    protected $store = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        return $this->store[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key): bool
    {
        unset($this->store[$key]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $this->store = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $r = [];
        foreach ($keys as $key) {
            $r[$key] = $this->get($key, $default);
        }

        return $r;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key): bool
    {
        return isset($this->store[$key]);
    }
}
