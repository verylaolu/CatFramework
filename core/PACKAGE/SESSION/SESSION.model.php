<?php
/**
 * FW SESSION包
 * @category   FW
 * @package  framework
 * @subpackage  core/PACKAGE/SESSION
 * @author    陆春宇
 */
class _SESSION {

    public $SESSION;

    public function __construct($conf) {
        //判断日志功能是否支持SQL记录，选择性开启日志功能
        $baseConf = getConf();
        if($conf['TYPE'] == 'REDIS' && empty($baseConf['REDIS'])){
            $conf['TYPE']='default';
        }
        switch ($conf['TYPE']) {
            case 'REDIS':
                $REDIS = P('REDIS',  $baseConf['REDIS']);
                require_cache((dirname(__FILE__) . '/RedisSESSION.php'));
                $SESSION = REDISSession::getInstance($REDIS,$conf['NAME']);
                break;
            default:
                require_cache((dirname(__FILE__) . '/PhpSESSION.php'));
                $SESSION = PHPSession::getInstance($conf['NAME']);
                break;
        }
        $this->SESSION = $SESSION;
    }

}

class SESSIONclass {

    //错误日志
    private static $INSTANCES = array();
    private $errorfile = 'error_log';
    //请求日志
    private $accessfile = 'access_log';
    private $exceptionfile = 'exception_log';
    private $sqllogfile = 'sql_log';
    private $sqlLogState = false;
    //当前目录地址
    private $path;
    //请求方式
    private $method = 'POST';

    //错误级别       
    const INFO = 1;
    const WARN = 2;
    const ERROR = 3;
    const FATAL = 4;
    //服务控制
    const SERVER = 500;
    const CLIENT = 400;

    //获得对象,初始化，日志路径，日志名
    public function __construct($conf) {
        $day = date('d', time());
        $this->errorfile = $conf['ERROR_NAME'] . '_' . $day . '.log';
        $this->accessfile = $conf['ACCESS_NAME'] . '_' . $day . '.log';
        $this->exceptionfile = $conf['EXCEPTION_NAME'] . '_' . $day . '.log';
        $this->sqllogfile = $conf['SQL_NAME'] . '_' . $day . '.log';
        $this->sqlLogState = $conf['SQL_STATE'];

        $this->path = $conf['PATH'];
    }

    public static function getInstance($conf, $name = 'log') {
        if (array_key_exists($name, self::$INSTANCES)) {
            return self::$INSTANCES[$name];
        }
        $inst = new LOGclass($conf);
        self::$INSTANCES[$name] = $inst;
        return $inst;
    }

    //访问日志文件操作，需要传入手机端唯一标示符
    public function access($uid) {
        //获取来源类型
        $req_type = $this->type();
        //获取来源地址 
        $req_url = $this->address();
        //IP地址
        $ip = $this->getIP();
        //客户端信息
        $client = $this->user_agent();
        //mac地址
        $mac_add = $uid;
        //请求时间
        $date = date('Y-m-d H:i:s', time());
        if(is_array($_FILES)){
            $this->method == 'files';
        }
        //写入内容
        $content = '[type：]' . $this->method . '  ' . '[access_source:]' . $req_url . '  ' . '[time：]' . $date . '  ' . '[IP：]' . $ip . '  ' . '[client_information:]' . $client . ' ' . '[uid：]' . $mac_add;

        return $this->write($content, $this->accessfile);
    }

    //访问日志文件操作，需要传入手机端唯一标示符
    public function setsqllog($sql) {
        if (!$this->sqlLogState) {
            return true;
        }
        //获取来源地址 
        $req_url = $this->address();
        //获取来源类型
        $req_type = $this->type();
        //IP地址
        $ip = $this->getIP();
        //客户端信息
        $client = $this->user_agent();
        //请求时间
        $date = date('Y-m-d H:i:s', time());
        //写入内容
        $content = '[type:]' . $this->method . '  ' . '[access_source:]' . "http://" . $_SERVER ['HTTP_HOST'] . $_SERVER["REQUEST_URI"] . "\r\n" . '['.$_SERVER["REQUEST_URI"].'][sql:]' . $sql . "\r\n" . '[time:]' . $date . '  ' . '[IP:]' . $ip . '  ' . '[client_information:]' . $client;

        return $this->write($content, $this->sqllogfile);
    }
    //来源地址 
    public function address() {
        $url = "http://" . $_SERVER ['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
        $this->type();
        if ($this->method == 'post') {
            $parm = '';
            foreach ($_POST as $k => $v) {
                $parm .= $k . ' = ' . $v . '  &  ';
            }
            foreach ($_FILES as $key => $val) {
                $file_info = '';
                foreach($val as $k=>$v){
                    $file_info.= $k .' = '.$v.'  &  ';
                }
                $parm .= $key . ' = <<<' .trim( $file_info,'  &  ') . '>>>  &  ';
            }
            $url = $url . '  ?  ' . trim($parm, '  &  ');
        }
        return $url;
    }

    //获得来源的传递方式
    public function type() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->method = 'post';
        } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->method = 'get';
        } elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            $this->method = 'put';
        } elseif ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            $this->method = 'head';
        }
    }

    //获取客户端IP地址
    public function getIP() {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    //写日志  要写的字符串和要写入的日志名
    public function write($content, $filename) {
        $content = $content;
        $filepath = $this->path;
        $mon = date('Ym', time());
        $day = date('d', time());
        $res = $this->folder($filepath . $mon);
        if ($res) {
            $fo = fopen($res . '/' . $filename, 'a+');
            fwrite($fo, $content . "\r\n\r\n\r\n");
            fclose($fo);
            return TRUE;
        } else {
            echo '目录创建失败';
        }
    }

    //文件操作 判断文件是否存在    传递目录地址完整目录地址,不包含文件名
    public function folder($folder_add) {
        $folder = $folder_add;
        if (!is_dir($folder)) {
            mkdir($folder, 0777);
            return $folder;
        }
        return $folder;
    }

    //判断客户端信息
    public function user_agent() {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    //错误级别        
    public function getlist($level) {

        switch ($level) {
            case LOGclass::INFO:
                return "INFO";
            case LOGclass::WARN:
                return "WARN";
            case LOGclass::ERROR:
                return "ERROR";
            case LOGclass::FATAL:
                return "FATAL";
            default:
                return "LOG";
        }
    }

    //错误级别
    public function getServicelist($level) {

        switch ($level) {

            case LOGclass::SERVER:
                return "SERVER";
            case LOGclass::CLIENT:
                return "CLIENT";
            default:
                return "SERVER";
        }
    }

    //错误日志列表  错误级别，错误类型，错误内容
    public function err($err_lev, $err_con) {
        //错误级别,自定义错误级别
        $error_lev = $err_lev;
        //错误类型
        $error_type = $this->getlist($err_lev);
        //错误内容
        $error_con = $err_con;
        //IP地址
        $ip = $this->getIP();
        //客户端信息
        $client = $this->user_agent();
        //错误时间
        $date = date('Y-m-d H:i:s', time());
        //写入错误文件的内容
        $content = '[error_lev：]' . $error_lev . '  ' . '[error_type：]' . $error_type . '  ' . '[error_content：]' . $error_con . '  ' . '[ip:]' . $ip . ' ' . '[client_information:]' . $client . '   ' . '[time:]' . $date . '   ';
        return $this->write($content, $this->errorfile);
    }

    //错误日志列表  错误级别，错误类型，错误内容
    public function setException($code, $msg) {
        //错误时间
        $date = date('Y-m-d H:i:s', time());
        //写入错误文件的内容
        $content = '[Exception_type：] ' . $this->ExceptionLev($code) . ' [Exception_code：]' . $code . '  ' . '[msg：]' . $msg . '  ' . '[time:]' . $date . '   ';
        return $this->write($content, $this->exceptionfile);
    }

    function ExceptionLev($code) {
        $code = explode('.', $code);
        $type = $this->getServicelist($code['0']) . '.';
        $type .= $this->getlist($code['1']);
        return $type;
    }

}
