<?php

/**
 * FW 公共函数文件
 * @category   FW
 * @package  framework
 * @subpackage  common
 * @author    陆春宇
 */

/**
 * 优化requice 方法
 * @staticvar array $_importFiles 是否饮用缓冲区
 * @param type $filename requice文件路径
 * @return type true/false
 */
function require_cache($filename) {
    static $_importFiles = array();
    $filepath            = realpath($filename);

    // 如果找到不到文件则报异常，避免不知道发生了什么问题.
    if ($filepath) {
        $filename = $filepath;
    } else {
        throw new FWException("Can not find '$filename'", 500);
    }

    if (!isset($_importFiles[$filename])) {
        $_obj                    = require $filename;
        $_importFiles[$filename] = $_obj;
        return $_obj;
    }

    return $_importFiles[$filename];
}

/**
 * 浏览器格式化输出，优化var_dump
 * @param array $var
 * @param type $echo
 * @param type $label
 * @param type $strict
 * @return null|string
 */
function dump($var, $echo = true, $label = null, $strict = true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = "<pre>" . $label . htmlspecialchars($output, ENT_QUOTES) . "</pre>";
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    } else
        return $output;
}
function pr($var){
    echo '<pre>';
    print_r($var);
}

/**
 * 时间计算，精确毫秒
 * @staticvar array $_info
 * @param type $start
 * @param type $end
 * @param type $dec
 * @return type
 */
function ext($start, $end = '', $dec = 3) {
    static $_info = array();
    if (!empty($end)) {
        if (!isset($_info[$end])) {
            $_info[$end] = microtime(TRUE);
        }
        return number_format(($_info[$end] - $_info[$start]), $dec);
    } else {
        $_info[$start] = microtime(TRUE);
    }
}

//spl_autoload_register('autoload');

/**
 * 自动加载，
 * @param str $classname
 * @return obj
 */
function __autoload($classname) {
    global $app_path;
    if (!$app_path) {
        $app_path = LIB_PATH;
    }
    if (substr($classname, -10) == "Controller") {
        require_cache(FLIB_PATH . '/controllers/Controller.php');
        require_cache($app_path . '/controllers/' . $classname . '.php');
    } elseif (substr($classname, -6) == "Action") {
        require_cache(FLIB_PATH . '/action/Action.php');
        require_cache($app_path . '/action/' . $classname . '.php');
    } elseif (substr($classname, -6) == "Object") {
        require_cache(FLIB_PATH . '/object/Object.php');
        require_cache($app_path . '/object/' . $classname . '.php');
    } elseif (substr($classname, -5) == "Model") {
        require_cache(FLIB_PATH . '/model/Model.php');
        require_cache($app_path . '/model/' . $classname . '.php');
    } elseif (substr($classname, 0, 3) == 'PHP') {
        return; // PHPUnit 等需要走他自己的 autoload. Modify by HuangHong, 2014/6/26
    } else {
        require_cache($classname);
    }
    return;
}

/**
 * 实例其他APP的LIB对象（危险方法，项目分布式需谨慎对待）
 * @param str $APP  项目名称
 * @param classname $classname 类名
 * @return obj
 */
function A_CONSTRUCT($APP, $classname) {
    global $app_path;
    $app_path  = DIR_PATH . '/app_' . $APP . '/lib';
    $classname = new $classname;
    return $classname;
}

function A_DESTRUCT() {
    global $app_path;
    $app_path = '';
    return true;
}

/**
 * 获取框架功能包
 * @param classname $package
 * @param config $data
 * @return obj
 */
function P($package, $data = '') {
    require_cache(FW_PATH . "/core/PACKAGE/$package/$package.model.php");
    if (empty($$package)) {
        $classname = '_' . $package;
        $$package  = new $classname($data);
    }
    return $$package->$package;
}

//function assignment($)
/**
 * 模块执行路由
 * @param string $appname
 */
function MODULE($appname) {
    if (!file_exists(DIR_PATH . '/app_' . $appname . '/index.php')) {
        $appname = BASE_APP;
    }
    require_cache(DIR_PATH . '/app_' . $appname . '/index.php');
}

/**
 * 跳转
 * @param str $url
 */
function header_go($url = '') {
    $url = $url ? $url : $_SERVER["HTTP_REFERER"];
    header("Location: " . $url);
    exit;
}

/**
 * 获取配置文件
 * @return arr
 */
function getConf($key = '') {
    static $conf = array();

    if (!$conf) {
        $conf = require_cache(APP_PATH_CONFIG . 'app_config.php');
    }
    if (!empty($key)) {
        return $conf[$key];
    }
    return $conf;
}

/**
 * 数组VALUE转换为字符串
 * @param mixed $v
 * @return mixed
 */
function _totext($v) {
    if (is_array($v)) {
        return array_map('_totext', $v);
    }
    return (string) $v;
}

/**
 * 封装的return
 * @param int $code
 * @param json $content
 */
function _return($code, $content = array()) {
    if (is_array($content) && isset($content['uid'])) {
        $content['auth_session'] = encryptionAuthSession($content['uid']);
    }
    $state    = require_cache(APP_PATH . '/common/requeststate.php');
    $info     = array('code'    => $code,
        'msg'     => $state[$code],
        'content' => $content);
    $conf     = getConf();
    $logModel = P('LOG', $conf['LOG']);
    if ($logModel) {
        $logModel->access_json(isset($content['uid']) ? $content['uid'] : 'guest', json_encode(_totext($info)));
    }
    //echo 11;var_dump(_totext($info));echo '<hr />';
    exit(json_encode(_totext($info)));
}
function ajax_return($array){
    exit(json_encode(_totext($array)));
}

/**
 * md5配合字符串加密
 * 20140625 wtb
 * @param $password  string
 * @param null $str  string
 * @return string
 */
function encryptionPassword($password, $str = null) {
    return md5($password . $str);
}

/**
 * AuthSession加密方法
 * @param str $info
 * @return str
 */
function encryptionAuthSession($info) {
    return base64_encode($info);
}

/**
 * AuthSession解密方法
 * @param str $info
 * @return str
 */
function decryptionAuthSession($info) {
    return base64_decode($info);
}

/**
 * 邮箱格式验证
 * @param type $user_email
 * @return boolean
 */
function check_is_email($user_email) {
    $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
    if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false) {
        if (preg_match($chars, $user_email)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 *  获取首字母
 *  @param string $str
 *  @return string
 */
function getPYFirstString($str) {
    require_cache(FLIB_PATH . '/function/str2py.function.php');
    $str2ty = new str2py();
    $c      = $str2ty->getFirstString($str);

    // 不是字母的字符均视作 #
    if (!preg_match('/^[a-z]$/i', $c)) {
        return '#';
    }
    return $c;
}

/**
 * 结果集排序
 *  @param array $arr
 *  @return array
 */
function getSortArr($arr, $index) {
    $keys = array();
    foreach ($arr as $key => $value) {
        $keys[] = $value[$index];
    }
    sort($keys);
    $keys = array_flip($keys);
    foreach ($keys as $k => $v) {
        foreach ($arr as $key => $value) {
            if ($k == $value[$index]) {
                $sortarr[$k]['index']  = $value[$index];
                $sortarr[$k]['list'][] = $value;
            }
        }
    }
    foreach ($sortarr as $key => $value) {
        $data[] = $value;
    }
    return $data;
}

/**
 * 获取中文字串的拼音首字符
 *
 * @param string $str
 * @return string
 */
function getPYInitials($str) {
    require_cache(FLIB_PATH . '/function/str2py.function.php');
    $str2ty = new str2py();
    return $str2ty->getInitials($str);
}

/**
 * 拼装URL
 * @param str $url
 * @param str/arr $param
 */
function assembleURL($url, $param) {
    $param_str = '';
    if (is_array($param)) {
        foreach ($param as $key => $value) {
            $param_str .= '&' . $key . '=' . $value;
        }
    } else {
        $param_str = $param;
    }
    if (strstr($url, "?")) {
        $url .= $param_str;
    } else {
        $url .= '?' . trim($param_str, '&');
    }
    return $url;
}

/**
 * 补全图片的url  20140711 wangtianbao
 * @param string $img
 * @return string
 */
function setImageUrl($img) {
    if (empty($img)) {
        return '';
    }

    $url = HOST;
    //判断是否存在
    if (strpos($img, "http://") === false) {
        $ret = $url . $img;
    } else {
        $ret = $img;
    }
    return $ret;
}

/**
 * base3Ï2加密方法
 * @param string $input
 * @return string
 */
function base32_encode($input) {
    $BASE32_ALPHABET = 'abcdefghijklmnopqrstuvwxyz234567';
    $output          = '';
    $v               = 0;
    $vbits           = 0;

    for ($i = 0, $j = strlen($input); $i < $j; $i++) {
        $v <<= 8;
        $v += ord($input[$i]);
        $vbits += 8;

        while ($vbits >= 5) {
            $vbits -= 5;
            $output .= $BASE32_ALPHABET[$v >> $vbits];
            $v &= ((1 << $vbits) - 1);
        }
    }

    if ($vbits > 0) {
        $v <<= (5 - $vbits);
        $output .= $BASE32_ALPHABET[$v];
    }

    return $output;
}

/**
 * base32解密方法
 * @param string $input
 * @return string
 */
function base32_decode($input) {

    $input  = strtolower($input);
    $output = '';
    $v      = 0;
    $vbits  = 0;

    for ($i = 0, $j = strlen($input); $i < $j; $i++) {
        $v <<= 5;
        if ($input[$i] >= 'a' && $input[$i] <= 'z') {
            $v += (ord($input[$i]) - 97);
        } elseif ($input[$i] >= '2' && $input[$i] <= '7') {
            $v += (24 + $input[$i]);
        } else {
            exit(1);
        }

        $vbits += 5;
        while ($vbits >= 8) {
            $vbits -= 8;
            $output .= chr($v >> $vbits);
            $v &= ((1 << $vbits) - 1);
        }
    }
    return $output;
}

/**
 * debug函数   wangtianbao 20141014
 * @param string/array $str
 */
function zdebug($str) {

    $path = DIR_PATH . '/public/dev_log/';

    if (!file_exists($path) || !is_writable($path)) {
        $res = mkdir($path, 0755, true);
    }

    $fp = $path . 'dev_log.txt';

    if (is_array($str)) {
        @file_put_contents($fp, $title . date('H:i:s') . ' ' . var_export($str, TRUE) . "\n", FILE_APPEND);
    } else {
        @file_put_contents($fp, $title . date('H:i:s') . " \n {$str}\n", FILE_APPEND);
    }

    //获取php最后发生的错误
    $err_mesg = error_get_last();
    if (!empty($err_mesg)) {
        @file_put_contents($fp, $title . date('H:i:s') . " \n " . var_export($err_mesg, TRUE) . "\n", FILE_APPEND);
    }
}

function user_agent() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Maxthon')) {
        $browser = 'Maxthon';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
        $browser = 'Weixin';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 12.0')) {
        $browser = 'IE12.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 11.0')) {
        $browser = 'IE11.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 10.0')) {
        $browser = 'IE10.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.0')) {
        $browser = 'IE9.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0')) {
        $browser = 'IE8.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0')) {
        $browser = 'IE7.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')) {
        $browser = 'IE6.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'NetCaptor')) {
        $browser = 'NetCaptor';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Netscape')) {
        $browser = 'Netscape';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Lynx')) {
        $browser = 'Lynx';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera')) {
        $browser = 'Opera';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox')) {
        $browser = 'Firefox';
    }
//    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) {
//        $browser = 'android';
//    }
    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')) {
        $browser = 'iPhone';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'iPod')) {
        $browser = 'iPod';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
        $browser = 'iPad';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Android')) {
        $browser = 'Android';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) {
        $browser = 'Google';
    } else {
        $browser = 'other';
    }
    return $browser;
}

function checkPhone() {
    $pohon_array = array('iPhone', 'iPod', 'iPad', 'Android', 'Weixin');
    $agent       = user_agent();
    if (in_array($agent, $pohon_array)) {
        return $agent;
    } else {
        return false;
    }
}

/**
 * PHP获取字符串中英文混合长度
 * @param $str string 字符串
 * @return 返回长度，1中文=1位，2英文=1位
 */
function strLength($str) {
    $str   = iconv('utf-8', 'gb2312', $str);
    $num   = strlen($str);
    $cnNum = 0;
    for ($i = 0; $i < $num; $i++) {
        if (ord(substr($str, $i + 1, 1)) > 127) {
            $cnNum++;
            $i++;
        }
    }
    $enNum  = $num - ($cnNum * 2);
    $number = ($enNum / 2) + $cnNum;
    return ceil($number);
}

/**
 * 统计UTF-8编码的字符长度
 * 一个中文，一个英文都为一个字
 * @param string $str 字符串
 * @return int 字符串长度
 */
function utf8_strlen($str) {
    return preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $dummy);
}

/**
 * 中文截取函数
 * 一个中文，一个英文都为一个字
 * @param string $string 被截取的字符串
 * @param int $len 被截取的长度
 * @param boolean $slh 是否有省略号
 * @param int $start 从第几个字开始截取
 * @return string 截取后的字符串
 */
function utf8_substr($string, $len = 14, $slh = 0, $start = 0) {
    if ($slh AND utf8_strlen($string) > $len) {
        $str_slh = '…';
    }
    return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $start . '}' .
                    '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $len . '}).*#s', '$1', $string) . $str_slh;
}

/**
 * 无限分级树获取
 *
 * @param array $arr                    分级栏目数组
 * @param int $pid                      最高查询父级该ID
 * @param str $db_id_str		数据库中ID字段名	默认：id
 * @param str $db_partent_str           数据库中父ID字段名	默认：partentid
 * @param str $title			数据库中名称ID名 	默认：title
 */
function tree($arr, $pid = 0, $db_id_str = 'id', $db_partent_str = 'partentid', $title = 'title', $get_type = '', $state_str = '', $state = null) {
    if (!$arr) {
        return false;
    }
    require_cache(FLIB_PATH . '/function/tree.function.php');
    $tree = new TREE();
    $data = $tree->getTree($pid, $arr, $db_id_str, $db_partent_str, $title, $get_type, $state_str, $state);
    return $data;
}
/**
 * 无限分级树    递归父级列表
 *
 * @param type $tree_list    一级数组
 * @param type $id_str      当前数组KEY
 * @param type $parent_str  当前数组父级KEY
 * @param type $start       当前数组ID
 * @param type $end         截止到ID
 * @return boolean
 */
function getParentTrue($tree_list,$id_str,$parent_str,$start,$end){
    if (!$tree_list) {
        return false;
    }
    require_cache(FLIB_PATH . '/function/tree.function.php');
    $tree = new TREE();
    $tree->getParentTrue($tree_list,$id_str,$parent_str,$start,$end);
    return $tree->info;
}

/**
 * 获取自定义短编码
 * @deprecated since version 1.0
 * @param str $str 编码字符串
 * @param int $length 获取长度
 * @param str $prefix 前缀
 * @param str $suffix 后缀
 * @return str
 */
function short_code($data = '', $length = 12, $prefix = '', $suffix = '') {
    $str       = is_array($data) ? implode('', $data) : $data;
    $secret    = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . $time[sec] . $time[usec] . mt_rand());
    $str .= $secret;
    $base32    = array(
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
        'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
        'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
        'y', 'z', '0', '1', '2', '3', '4', '5');
    $hex       = md5($prefix . $str . $suffix);
    $hexLen    = strlen($hex);
    $subHexLen = $hexLen / 8;
    $output    = array();
    for ($i = 0; $i < $subHexLen; $i++) {
        $subHex = substr($hex, $i * 8, 8);
        $int    = 0x3FFFFFFF & (1 * ('0x' . $subHex));
        $out    = '';
        for ($j = 0; $j < 6; $j++) {
            $val = 0x0000001F & $int;
            $out .= $base32[$val];
            $int = $int >> 5;
        }
        $output[] = $out;
    }
    $output = implode('', $output);
    $output = substr($output, 0, $length);
    return array('code' => $output, 'code_secret' => $secret);
}

function array_udiff_function($a, $b) {
    if ($a['id'] === $b['id']) {
        return 0;
    }
    return ($a > $b) ? 1 : -1;
}
function EnglishChar($str){
    $parrten = "/[a-zA-Z]+/";
    preg_match_all($parrten,$str,$arr);
    return  strtolower(implode('', $arr[0]));
}
/**
 * 判断是否POST请求
 *
 */
function is_post() {
    return 'post' == strtolower($_SERVER['REQUEST_METHOD']);
}

/**
 * 判断请求是否是AJAX请求,只支持jquery
 * @param boolean $exit 如果不是，是否自动停止程序执行
 * @return boolean
 */
function is_ajax($exit = false) {
    $result = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ? true : false;
    if ($result === false && $exit)
        exit('Access Deny');
    return $result;
}

