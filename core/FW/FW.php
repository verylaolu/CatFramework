<?php
/**
 * FW 框架初始化
 * @category   FW
 * @package  framework
 * @subpackage  core/FW
 * @author    陆春宇
 */
class FW {

    public static $conf = null;

    function __construct() {
        self::$conf = getConf();
    }

    /**
     * 执行程序
     * @return type
     */
    function init() {

        $obj = Route::getRoute();
        $Model_name = $obj->module . 'Controller';
        $Action_name = $obj->action;
        if (!file_exists(LIB_PATH . '/controllers/' . $Model_name . '.php')) {
            header_go(self::$conf['404']);
        }

        $Model = new $Model_name();
        if (!method_exists($Model, $Action_name)) {
            header_go(self::$conf['404']);
        }
        
        // 动作异常处理.
        try {
            return $Model->$Action_name();
        } catch (FWException $ex) {
            // 发现未被处理的框架异常则返回错误代码
            $code = $ex->getCode();
            $msg  = $ex->getMessage();
            if (!$code) {
                $code = 500;
            }
            if (!$msg ) {
                _return($code);
            }
            echo json_encode(array(
                'code' => $code,
                'msg'  => $msg ,
            ));
        } catch (Exception $ex) {
            // 发现未知异常记录到日志
            $code = 500;
            $msg  = "Error(".$ex->getCode()."): ".$ex->getMessage();
            echo json_encode(array(
                'code' => $code,
                'msg'  => $msg ,
            ));
            
            
            $log  = P(LOG, self::$conf['LOG']);
            if($log){
                $log->setException($code, $msg);
            }
            
        }
    }

}
