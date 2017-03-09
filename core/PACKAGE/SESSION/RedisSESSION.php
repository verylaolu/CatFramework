<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SESSIONclass
 *
 * @author luchunyu
 */
class REDISSession {

    private static $INSTANCES = array();
    private $redis;
    private $session_id;

    public function __construct($redis) {
        $this->SESSION_START();
        $this->redis      = $redis;
        $this->session_id = 'session-' . session_id();
        session_name();
    }

    /**
     * 获取单例
     * @param string $name 配置名
     * @return Mysql
     */
    public static function getInstance($redis, $name = 'default') {
        if (array_key_exists($name, self::$INSTANCES)) {
            return self::$INSTANCES[$name];
        }
        $inst                   = new REDISSession($redis);
        self::$INSTANCES[$name] = $inst;
        return $inst;
    }
    public function SESSION_START(){
        if(!isset($_SESSION)){
            session_start();
        }
        return true;
    }

    public function SET_SESSION($key, $value, $time = 0) {
        $this->redis->hSet($this->session_id, $key, json_encode($value));
        if($time>0){
            $this->redis->expire($this->session_id,$time);
        }
        return true;
    }

    public function GET_SESSION($key) {
        $value =  $this->redis->hGet($this->session_id, $key);
        return json_decode($value,true);
    }
    public function DEL_SESSION($key) {
        return $this->redis->hDel($this->session_id, $key);
    }

    public function DESTROY_SESSION() {
        setcookie(session_name(),"",time()-3600, '/');
        return $this->redis->delete($this->session_id);
    }

}
