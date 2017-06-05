<?php
/**
 *
 * @date         : 2015/07/30
 * @description  : a cache utility. support  APC, memcache
 * @usage        :
 *  initial:
 *               $config = [
 *                  'driver'     => 'memcache',
 *                  'mem_host'   => 'localhost',
 *                  'mem_port'   => 11211,
 *                  'expire'     => 3600,
 *                  'p_timeout'  => 2,   # for future
 *                  'isCompress' => 0
 *                  # You can use MEMCACHE_COMPRESSED constant as flag value if you want to use on-the-fly compression (uses zlib).
 *               ];
 *               $obj = new Tcache($config);
 *   set value:
 *               $obj->set($key,$value,$expire);
 *   get value:
 *               $obj->get($key);
 *   clear cache:
 *               $obj->clear();
 * @author       : Tiger <DropFan@Gmail.com>
 */

class Tcache
{

    private $driver;
    private $mObj;
    private $config = [
        'driver'     => 'memcache',
        'mem_host'   => 'localhost',
        'mem_port'   => 11211,
        'expire'     => 3600,
        'p_timeout'  => 2,
        'isCompress' => 0,
    ];

    public function __construct($config = [])
    {
        if (is_array($config) && isset($config['driver'])) {
            foreach ($this->config as $k => $v) {
                $this->config[$k] = isset($config[$k]) ? $config[$k] : $v;
            }
        } else {
            die('ERROR! Missing config.');
        }
        switch ($config['driver']) {
            case 'memcache':
                if (!function_exists('memcache_connect')) {
                    die('Memcache functions not available');
                }
                $this->driver = 'memcache';

                $host = $config['mem_host'];
                $port = $config['mem_port'];

                $this->mObj = new Memcache();
                $this->mObj->connect($host, $port);
                break;
            case 'apc':
                if (!function_exists('apc_add')) {
                    die('Apc functions not available');
                }
                $this->driver = 'apc';
                break;
            default:
                die('No driver available');
                break;
        }
    }

    public function get($key)
    {
        switch ($this->driver) {
            case 'memcache':
                $value = $this->mObj->get($key);
                break;
            case 'apc':
                $value = apc_fetch($key);
                break;
            default:
                return false;
                break;
        }

        return @unserialize($value);
    }

    public function set($key, $value, $expire = 0)
    {
        empty($expire) && $expire = $this->config['expire'];
        $value                    = @serialize($value);
        switch ($this->driver) {
            case 'memcache':
                $flag = $this->config['isCompress'];
                $ret  = $this->mObj->set($key, $value, $flag, $expire);
                break;
            case 'apc':
                $ret = apc_store($key, $value, $expire);
                break;
            default:
                return false;
                break;
        }

        return $ret;
    }

    public function delete($key, $timeout = 0)
    {
        switch ($this->driver) {
            case 'memcache':
                $ret = $this->mObj->delete($key, $timeout);
                break;
            case 'apc':
                $ret = apc_delete($key);
                break;
            default:
                return false;
                break;
        }

        return $ret;
    }

    public function clear()
    {
        switch ($this->driver) {
            case 'memcache':
                $ret = $this->mObj->flush();
                break;
            case 'apc':
                $ret = apc_clear_cache();
                break;
            default:
                return false;
                break;
        }

        return $ret;
    }

    public function close()
    {
        switch ($this->driver) {
            case 'memcache':
                $ret = $this->mObj->close();
                break;
            case 'apc':
                $ret = true;
                break;
            default:
                return false;
                break;
        }

        return $ret;
    }

    public function __destruct()
    {
        switch ($this->driver) {
            case 'memcache':
                $this->close();
                break;
            case 'apc':
                break;
            default:
                break;
        }
    }
}
