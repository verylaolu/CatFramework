<?php

/**
 * FW SMS 连接程序�
 * @category   FW
 * @package  framework
 * @subpackage  core/PACKAGE/SMS

 */
class _SMS {

    public $SMS;
    public $CLT;

    function __construct($conf) {
        define('SCRIPT_ROOT', dirname(__FILE__) . '/');
        require_once SCRIPT_ROOT . 'include/Client.php';

        /**
         * 网关地址
         */
        $gwUrl = 'http://sdkhttp.eucp.b2m.cn/sdk/SDKService';

        /**
         * 序列号,请通过亿美销售人员获取
         */
        $serialNumber = '3SDK-EMY-0130-JITLR';

        /**
         * 密码,请通过亿美销售人员获取
         */
        $password = '154560';

        /**
         * 登录后所持有的SESSION KEY，即可通过login方法时创建
         */
        $sessionKey = '123456';

        /**
         * 连接超时时间，单位为秒
         */
        $connectTimeOut = 2;

        /**
         * 远程信息读取超时时间，单位为秒
         */
        $readTimeOut = 10;

        /**
          $proxyhost		可选，代理服务器地址，默认为 false ,则不使用代理服务器
          $proxyport		可选，代理服务器端口，默认为 false
          $proxyusername	可选，代理服务器用户名，默认为 false
          $proxypassword	可选，代理服务器密码，默认为 false
         */
        $proxyhost = false;
        $proxyport = false;
        $proxyusername = false;
        $proxypassword = false;

        $that = new Client($gwUrl, $serialNumber, $password, $sessionKey, $proxyhost, $proxyport, $proxyusername, $proxypassword, $connectTimeOut, $readTimeOut);
        /**
         * 发送向服务端的编码，如果本页面的编码为GBK，请使用GBK
         */
        $that->setOutgoingEncoding("UTF-8");

        $this->SMS = $this;
        $this->CLT = $that;
    }
   
    /**
     * 发送短信
     * @param string $msg
     * @param array|string $phones
     * @return int
     */
    function send($msg, $phones) {
        if (!is_array($phones)) {
            $phones = explode(',', $phones);
            $phones = array_map("trim", $phones);
        }
        $phones = array_unique($phones);
        $stat1 = $this->CLT->login();
	$stat2 = $this->CLT->sendSMS($phones, "【会议邦】".$msg);
        $balan = $this->CLT->getBalance();
        return array($stat1, $stat2, $balan);
    }

}
