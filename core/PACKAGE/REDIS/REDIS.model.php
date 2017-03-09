<?php
/**
 * FW REDIS包
 * @category   FW
 * @package  framework
 * @subpackage  core/PACKAGE/REDIS
 * @author    陆春宇
 */
class _REDIS{
    public $REDIS;
    public function __construct($conf) {
        $this->REDIS = redisClient::getInstance($conf,'redis');
    }


}
class redisClient{
    private static $INSTANCES = array();
    private $_redis = NULL;
    private $_ip=NULL;
    private $_port=NULL;
    private $_coon=NULL;

    public function __construct($conf) {
        $this->_ip = $conf['REDIS_HOST'];
        $this->_port = $conf['REDIS_PORT'];
        $this->_timeout = $conf['REDIS_TIMEOUT']?$conf['REDIS_TIMEOUT']:5;
        self::connect();

    }

    public static function getInstance($conf, $name = 'redis') {
        if (array_key_exists($name, self::$INSTANCES)) {
            return self::$INSTANCES[$name];
        }
        $inst = new self($conf);
        self::$INSTANCES[$name] = $inst;
        return $inst;
    }

    public function connect() {
        if(!class_exists('Redis')){
            return null;
        }
        if(empty($this->_redis))
        {
            $this->_redis = new Redis();
        }
       
        $this->_coon = $this->_redis->pconnect($this->_ip, $this->_port,$this->_timeout);
        if ($this->_coon === false) {
            throw new FWException('redis is down', '500.1');
        }
       
    }

    public function set($key, $val, $ttl=null)
    {
        if(!class_exists('Redis') || !$this->_coon) {
            return null;
        }
        $val = json_encode($val);
        if(empty($ttl)){
            $this->_redis->set($key, $val);
        }
        else {
            $this->_redis->setex($key, $ttl, $val);
        }
    }

    public function get($key)
    {
        if(!class_exists('Redis') || !$this->_coon) {
            return null;
        }
        $val = $this->_redis->get($key);
        $val = json_decode($val,true);
        return $val;
    }

    public function delete($key)
    {
        if(!class_exists('Redis') || !$this->_coon) {
            return null;
        }
        return $this->_redis->delete($key);
    }

    public function hSet($key, $hashkey, $value)
    {
        if(!class_exists('Redis') || !$this->_coon) {
            return null;
        }
        return $this->_redis->hSet($key, $hashkey, json_encode($value));
    }

    public function hGet($key, $hashkey)
    {
        if(!class_exists('Redis') || !$this->_coon) {
            return null;
        }
        $data = $this->_redis->hGet($key, $hashkey);
        return json_decode($data,true);
    }

    public function hDel($key, $hashkey)
    {
        if(!class_exists('Redis') || !$this->_coon) {
            return null;
        }
        return $this->_redis->hDel($key, $hashkey);
    }

    public function expire($key, $ttl)
    {
        if(!class_exists('Redis') || !$this->_coon) {
            return null;
        }
        return $this->_redis->expire($key, $ttl);
    }

    public function exists($key)
    {
        if(!class_exists('Redis') || !$this->_coon) {
            return false;
        }
        return $this->_redis->exists($key);
    }
    public function keys($key) {
        if (!class_exists('Redis') || !$this->_coon){
            return false;
        }
        return $this->_redis->keys($key);
    }
}
