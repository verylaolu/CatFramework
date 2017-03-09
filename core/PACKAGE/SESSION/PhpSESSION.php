<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PHPSession
 *
 * @author luchunyu
 */
class PHPSession {
    
    private static $INSTANCES = array();
    public function __construct() {
        $this->SESSION_START();
    }
    /**
     * 获取单例
     * @param string $name 配置名
     * @return Mysql
     */
    public static function getInstance($name = 'default') {
        if (array_key_exists($name, self::$INSTANCES)) {
            return self::$INSTANCES[$name];
        }
        $inst = new PHPSession();
        self::$INSTANCES[$name] = $inst;
        return $inst;
    }
    public function SESSION_START(){
        if(!isset($_SESSION)){
            session_start();
        }
        return true;
    }
    public function SET_SESSION($key,$value,$time=0){
        $_SESSION[$key]=$value;
//        if ($time && isset($_COOKIE[session_name()])) {
//            setcookie(session_name(), '', time()+$time, '/');
//        }
        return true;
    }
    public function GET_SESSION($key){
        return $_SESSION[$key];
    }
    public function DEL_SESSION($key){
        unset($_SESSION[$key]);
    }
    public function DESTROY_SESSION(){
        session_destroy();
        return true;
    }
}
