<?php
/**
 * FW 路由功能
 * @category   FW
 * @package  framework
 * @subpackage  core/FW
 * @author    陆春宇
 */
class Route {
    /**
     * URL解析
     * @return OBJ
     */
    static function getRoute() {
        
        $APP_CONF = getConf('CONF');
        $DEFAULT_APP=isset($APP_CONF['DEFAULT_APP'])?$APP_CONF['DEFAULT_APP']:'index';
        $DEFAULT_MODULE=isset($APP_CONF['DEFAULT_MODULE'])?$APP_CONF['DEFAULT_MODULE']:'index';
        $DEFAULT_ACTION=isset($APP_CONF['DEFAULT_ACTION'])?$APP_CONF['DEFAULT_ACTION']:'index';
        
        $app = !empty($_REQUEST['app'])?$_REQUEST['app']:$DEFAULT_APP;
        $module = !empty($_REQUEST['model'])?$_REQUEST['model']:$DEFAULT_MODULE;
        $action = !empty($_REQUEST['action'])?$_REQUEST['action']:$DEFAULT_ACTION;
        $obj ='';
        $obj = (object)$obj;
        $obj->app = trim($app, '/');
        $obj->model = ucfirst(trim($module, '/'));
        $obj->action = APP_WEBSERVER_PREFIX.trim($action, '/');
        $obj->action_url=trim($action, '/');
        return $obj;
    }
    /**
     * 部分参数入口过滤
     * @return null
     */
    static function checkRoute() {
        $_REQUEST = Route::checkRequest($_REQUEST);
        $_GET = Route::checkRequest($_GET);
        $_POST = Route::checkRequest($_POST);
        return;
    }

    /**
     * 过滤ASCII码从0-28的控制字符
     * @param type $requst
     * @return type
     */
    static function checkRequest($requst) {
        foreach ($requst as $key => $value) {
            // request 过来的也可能是数组, 本应递归处理才对, 现先跳过, 下方的再赋值的 key 也不对
            if (is_array($value)) {
                continue;
            }
            
            $value = Route::trim_unsafe_control_chars(Route::safe_replace(trim($value)));
            if ($value) {
                $requst[$key] = $value;
            }
        }
        return $requst;
    }

    /**
     * 安全过滤函数
     *
     * @param $string
     * @return string
     */
    static function safe_replace($string) {
        $string = str_replace('%20', '', $string);
        $string = str_replace('%27', '', $string);
        $string = str_replace('%2527', '', $string);
        $string = str_replace('*', '', $string);
        $string = str_replace('"', '&quot;', $string);
        $string = str_replace("'", '', $string);
        $string = str_replace('"', '', $string);
        $string = str_replace(';', '', $string);
        $string = str_replace('<', '&lt;', $string);
        $string = str_replace('>', '&gt;', $string);
        $string = str_replace("{", '', $string);
        $string = str_replace('}', '', $string);
        $string = str_replace('\\', '', $string);
        return $string;
    }

    /**
     * 过滤ASCII码从0-28的控制字符
     * @return String
     */
    static function trim_unsafe_control_chars($str) {
        $rule = '/[' . chr(1) . '-' . chr(8) . chr(11) . '-' . chr(12) . chr(14) . '-' . chr(31) . ']*/';
        return str_replace(chr(0), '', preg_replace($rule, '', $str));
    }

}
