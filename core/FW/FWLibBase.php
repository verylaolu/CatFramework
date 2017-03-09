<?php
/**
 * FW 公共基类。所有层级均可以使用的封装
 * @category   FW
 * @package  framework
 * @subpackage  core/FW
 * @author    陆春宇
 */
abstract class FWLibBase {
    public $_ROUTE;
    public $conf;
    protected function __construct(){
        $this->_ROUTE = Route::getRoute();
        $this->conf = getConf();
//        $this->SET_PACKAGE('SESSION');
    }
    protected function SET_PACKAGE($package) {
        $conf = isset($this->conf[$package])?$this->conf[$package]:'';
        return $this->$package = P($package, $conf);
    }
    public function GET_PACKAGE($package){
        return $this->_PACKAGE[$package];
    }
    public function SET_SESSION($key,$value,$time=0){
        $this->SESSION->SET_SESSION($key,$value,$time);
        return true;
    }
    public function GET_SESSION($key){
        return $this->SESSION->GET_SESSION($key);
    }
    public function DEL_SESSION($key){
        return $this->SESSION->DEL_SESSION($key);
    }
    public function DESTROY_SESSION(){
        return $this->SESSION->DESTROY_SESSION();
    }
    
}
