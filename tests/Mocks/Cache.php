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
    public function get($key, $default = null)
    {
        return $this->store[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        $this->store[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        unset($this->store[$key]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->store = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null)
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
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return isset($this->store[$key]);
    }
}
