<?php

/// Expire the set cached value at the next graph generation
const CACHE_UNTIL_NEXT_GRAPH = -25;

require ROOT . '../private_html/Predis/Autoloader.php';

Predis\Autoloader::register();

/**
 * Handles caching, by default with memcached
 */
class Cache {

    /**
     * The handle to the caching object
     * @var Predis\Client
     */
    private $handle;

    /**
     * If we have attempted to connect to the cache store or not
     */
    private $attemptedConnection = false;

    public function __construct() {
    }

    /**
     * Get the caching daemon handle
     */
    public function handle() {
        return $this->handle;
    }

    /**
     * @return TRUE if caching is enabled, otherwise FALSE
     */
    public function isEnabled() {
        global $config;
        return $config['cache']['enabled'];
    }

    /**
     * Connect to the caching engine
     */
    public function connect() {
        global $config;
        $this->attemptedConnection = true;

        if ($this->isEnabled()) {
            $this->handle = new Predis\Client(array(
                'host' => $config['cache']['host'],
                'port' => $config['cache']['port'],
                'database' => $config['cache']['database']
            ));
        }
    }

    /**
     * Get an object from the cache
     * @param $key string
     * @return object The result
     */
    public function get($key) {
        if (!$this->isEnabled()) {
            return null;
        }

        if (!$this->attemptedConnection) {
            $this->connect();
        }

        $result = $this->handle->get($key);

        return $result != null ? gzdecode($result) : null;
    }

    /**
     * Store a key/value pair in the cache
     * @param $key string The key to store as
     * @param $value object The value to store
     * @param $expire int The number of seconds to expire in, 0 for forever
     * @return TRUE on success and FALSE on failure
     */
    public function set($key, $value, $expire = 0) {
        if (!$this->isEnabled()) {
            return false;
        }

        if (!$this->attemptedConnection) {
            $this->connect();
        }

        // Check for flags
        if ($expire == CACHE_UNTIL_NEXT_GRAPH) {
            $expire = strtotime('+30 minutes', getLastGraphEpoch());
        }

        $this->handle->set($key, gzencode($value));

        if ($expire > 0) {
            $this->handle->expireat($key, $expire);
        }

        return true;
    }

}