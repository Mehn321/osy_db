<?php

/**
 * Cache Class
 *
 * A lightweight, file-based caching system.
 * Stores serialized query results as flat files in a cache/ directory.
 * Inspired by Facebook's Fragment Caching pattern — fast reads on repeat visits.
 */
class Cache
{
    private string $cacheDir;
    private int $defaultTtl;

    /**
     * @param int $defaultTtl Default cache lifetime in seconds (default: 60s)
     */
    public function __construct(int $defaultTtl = 60)
    {
        $this->cacheDir = __DIR__ . '/../cache/';
        $this->defaultTtl = $defaultTtl;

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get a value from cache. Returns null on miss or expiry.
     */
    public function get(string $key): mixed
    {
        $file = $this->filePath($key);
        if (!file_exists($file)) {
            return null;
        }

        $data = @unserialize(file_get_contents($file));
        if ($data === false || !isset($data['expires_at'], $data['value'])) {
            return null;
        }

        if (time() > $data['expires_at']) {
            @unlink($file);
            return null;
        }

        return $data['value'];
    }

    /**
     * Store a value in cache.
     *
     * @param string $key   Unique cache key
     * @param mixed  $value Data to cache
     * @param int|null $ttl  TTL in seconds; null uses the default
     */
    public function set(string $key, mixed $value, ?int $ttl = null): void
    {
        $ttl  = $ttl ?? $this->defaultTtl;
        $data = serialize(['expires_at' => time() + $ttl, 'value' => $value]);
        file_put_contents($this->filePath($key), $data, LOCK_EX);
    }

    /**
     * Fetch from cache, or run $callback and cache its result.
     *
     * Usage:
     *   $result = $cache->remember('my_key', fn() => $db->fetchAll(...), 120);
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $cached = $this->get($key);
        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    /**
     * Delete a specific cache entry.
     */
    public function forget(string $key): void
    {
        $file = $this->filePath($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Delete all cache entries whose key starts with $prefix.
     * Useful for invalidating a group (e.g., forget('dashboard_') clears all dashboard caches).
     */
    public function forgetPrefix(string $prefix): void
    {
        $pattern = $this->cacheDir . $this->sanitizeKey($prefix) . '*.cache';
        foreach (glob($pattern) as $file) {
            @unlink($file);
        }
    }

    /**
     * Flush the entire cache.
     */
    public function flush(): void
    {
        foreach (glob($this->cacheDir . '*.cache') as $file) {
            @unlink($file);
        }
    }

    // -------------------------------------------------------------------------

    private function filePath(string $key): string
    {
        return $this->cacheDir . $this->sanitizeKey($key) . '.cache';
    }

    private function sanitizeKey(string $key): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
    }
}
