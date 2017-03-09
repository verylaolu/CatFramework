<?php
/**
 * FW 基类：对象实例层
 * 提供对象层使用的插件功能，被继承后可直接使用
 *
 * @category   FW
 * @package  framework
 * @subpackage  lib/object
 * @author    陆春宇
 */
abstract class Object extends FWLibBase{
    /**
     * 修改或创建对象数据
     * @param str $key
     * @param str $value
     * @return BOOL
     */
    public function set($key, $value) {
        $this->$key = $value;
        return ture;
    }

    /**
     * 获取对象某个数据
     * @param str $key
     * @return value
     */
    public function get($key) {
        return $this->$key;
    }

    /**
     * 获取对象已经存在的信息
     * @return array
     */
    public function getArray() {
        $array = $this;
        $array = (array) $array;
        foreach($array as $key=>$value){
            if($value===NULL){
                unset($array[$key]);
            }
        }
        unset($array['id']);
        return $array;
    }

    /**
     * 获取对象已经存在的信息
     * @return array
     */
    public function getInfo() {
        $array = (array) $this;
        foreach($array as $key=>$value){
            if($value===NULL){
                unset($array[$key]);
            }
        }
        return $array;
    }
}
