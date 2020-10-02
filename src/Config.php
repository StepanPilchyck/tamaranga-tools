<?php

namespace Tamaranga\Tools;

class Config
{
    private static $instances = [];

    private $_config = null;

    private $_load = null;

    protected function __construct() { }

    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
    
    public static function getInstance($config): Config
    {
        $cls = static::class;

        if ( ! isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        self::$instances[$cls]->_config = $config;

        return self::$instances[$cls];
    }

    public function save(string $key, $value = false, $dynamic = false)
    {
        if ( ! method_exists($this->_config, 'save')) {
            return null;
        }

        if ( ! is_null($this->_load)) {
            $this->_load[$key] = $value;
        }

        return $this->_config::save($key, $value, $dynamic);
    }

    public function load(string $key)
    {
        if ( ! method_exists($this->_config, 'load')) {
            return null;
        }

        if (is_null($this->_load)) {
            $this->_load = $this->_config::load();
        }

        if ( ! isset($this->_load[$key])) {
            $this->_load[$key] = null;
        }
        
        return $this->_load[$key];
    }
}