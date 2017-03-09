<?php

/**
 * FW CURL包
 * @category   FW
 * @package  framework
 * @subpackage  core/PACKAGE/CURL
 * @author    陆春宇
 */
class _CURL {

    public $CURL;

    public function __construct() {
        $this->CURL = CURLclass::getInstance('curl');
    }

}

class CURLclass {

    public $string;
    private static $INSTANCES = array();

    public static function getInstance($name = 'default') {
        if (array_key_exists($name, self::$INSTANCES)) {
            return self::$INSTANCES[$name];
        }

        $inst                   = new CURLclass();
        self::$INSTANCES[$name] = $inst;
        return $inst;
    }

    public function makeReqStr($req) {
        $str = '';
        foreach ($req as $key => $value) {
            $str .= $key . '=' . $value . '&';
        }
        return trim($str, '&');
    }

    public function get($uri, $req = NULL) {
        $req = $this->makeReqStr($req);
        $url = $uri . '?' . $req;

        $cuh = curl_init();
        curl_setopt($cuh, CURLOPT_URL, $url);
        curl_setopt($cuh, CURLOPT_HEADER, 0);
        curl_setopt($cuh, CURLOPT_TIMEOUT, 90);
        curl_setopt($cuh, CURLOPT_RETURNTRANSFER, 1);
        $rsp = curl_exec($cuh);
        if ($rsp === false) {
            $error = curl_error($cuh);
            $errno = curl_errno($cuh);
            curl_close($cuh);
            throw new FWException($error, $errno);
        } else {
            curl_close($cuh);
        }

        $rsp = json_decode($rsp, true);
        return $rsp;
    }

    public function post($uri, $req) {
        $url = $uri;

        $cuh = curl_init();
        curl_setopt($cuh, CURLOPT_URL, $url);
        curl_setopt($cuh, CURLOPT_HEADER, 0);
        curl_setopt($cuh, CURLOPT_TIMEOUT, 90);
        curl_setopt($cuh, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cuh, CURLOPT_POST, 1);
        curl_setopt($cuh, CURLOPT_POSTFIELDS, $req);
        $rsp = curl_exec($cuh); //echo $rsp;
        $req = json_encode($req);
//        file_put_contents(APP_PATH.'/curl.log', date('[Y-m-d] ')."\r\nURL: $uri\r\nREQ: $req\r\nRSP: $rsp\r\n\r\n", FILE_APPEND);

        if ($rsp === false) {
            $error = curl_error($cuh);
            $errno = curl_errno($cuh);
            curl_close($cuh);
            throw new FWException($error, $errno);
        } else {
            curl_close($cuh);
        }

        //打印string查看输入的Json  wangtianbao 20140806
        $this->string = $rsp;

        $rsp = json_decode($rsp, true); //var_dump($rsp);
        return $rsp;
    }

    public function upload($uri, $req, $files) {
        $url = $uri;

        foreach ($files as $n => $f) {
            $req[$n] = '@' . $f;
        }

        $cuh = curl_init();
        curl_setopt($cuh, CURLOPT_URL, $url);
        curl_setopt($cuh, CURLOPT_HEADER, 0);
        curl_setopt($cuh, CURLOPT_TIMEOUT, 90);
        curl_setopt($cuh, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cuh, CURLOPT_POST, 1);
        curl_setopt($cuh, CURLOPT_POSTFIELDS, $req);
        //curl_setopt($cuh, CURLOPT_UPLOAD, 1);
        $rsp = curl_exec($cuh);
        if ($rsp === false) {
            $error = curl_error($cuh);
            $errno = curl_errno($cuh);
            curl_close($cuh);
            throw new FWException($error, $errno);
        } else {
            curl_close($cuh);
        }

        $rsp = json_decode($rsp, true);
        return $rsp;
    }

    public function getHtml($url) {
        $user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh- CN; rv:1.9.0.5) Gecko/2008120122 Firefox/3.0.5 FirePHP/0.2.1";
        $ch         = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); //设置要访问的IP
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent); //模拟用户使用的浏览器 
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转  
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); //设置超时时间
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer  
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $result = curl_exec($ch);
        $html   = iconv('gbk', 'utf-8', $result);
        return $html;
    }

    public function getInfo($html, $partern) {
        preg_match_all($partern, $html, $array, PREG_SET_ORDER);
        return $array;
    }
    public function download_img($url,$file_path){
        require_cache(dirname(__FILE__) . '/DownloadImage.php');
        $DownModel = new DownloadImage($url, $file_path);
        return $DownModel->download();
    }

}
