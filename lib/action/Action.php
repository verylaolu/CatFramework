<?php
/**
 * FW ACTION基类,框架所有action 集成该类
 * @category   FW
 * @package  framework
 * @subpackage  lib/action
 * @author    陆春宇
 */
abstract class Action extends FWLibBase {
    protected $CURL;
    protected $UPLOAD;
    protected $IMAGE;
    protected $MAIL;
    protected $REDIS;
    protected $_PACKAGE;

    protected function __construct($REG_PACKAGE=array()) {
        parent::__construct();
        if(isset($REG_PACKAGE['CURL'])){
            $this->CURL = $this->SET_PACKAGE('CURL');
            unset($REG_PACKAGE['CURL']);
        }
        if(isset($REG_PACKAGE['IMAGE'])){
            $this->IMAGE = $this->SET_PACKAGE('IMAGE');
            unset($REG_PACKAGE['IMAGE']);
        }
        if(isset($REG_PACKAGE['UPLOAD'])){
            $this->UPLOAD = $this->SET_PACKAGE('UPLOAD');
            unset($REG_PACKAGE['UPLOAD']);
        }
        if(isset($REG_PACKAGE['MAIL'])){
            $this->MAIL = $this->SET_PACKAGE('MAIL');
            unset($REG_PACKAGE['MAIL']);
        }
        if(isset($REG_PACKAGE['REDIS'])){
            $this->REDIS = $this->SET_PACKAGE('REDIS');
            unset($REG_PACKAGE['REDIS']);
        }
        if(isset($REG_PACKAGE['SAM'])){
            $this->SAM = $this->SET_PACKAGE('SAM');
            unset($REG_PACKAGE['SAM']);
        }
        if(isset($REG_PACKAGE['MCC'])){
            $this->MCC = $this->SET_PACKAGE('MCC');
            unset($REG_PACKAGE['MCC']);
        }
        if(is_array($REG_PACKAGE)){
            foreach ($REG_PACKAGE as $key => $value) {
                $this->_PACKAGE[$key] = $this->SET_PACKAGE($key);
            }
        }
    }

    
    
    public function Redis_set($key, $val, $ttl=null)
    {
        self::checkREDIS();
        return $this->REDIS->set($key, $val, $ttl);
    }

    public function Redis_get($key)
    {
        self::checkREDIS();
        return $this->REDIS->get($key);
    }

    public function Redis_delete($key)
    {
        self::checkREDIS();
        return $this->REDIS->delete($key);
    }

    public function Redis_hSet($key, $hashkey, $value)
    {
        self::checkREDIS();
        return $this->REDIS->hSet($key, $hashkey, $value);
    }

    public function Redis_hGet($key, $hashkey)
    {
        self::checkREDIS();
        return $this->REDIS->hGet($key, $hashkey);
    }

    public function Redis_hDel($key, $hashkey)
    {
        self::checkREDIS();
        return $this->REDIS->hDel($key, $hashkey);
    }

    public function Redis_expire($key, $ttl)
    {
        self::checkREDIS();
        return $this->REDIS->expire($key, $ttl);
    }

    public function Redis_exists($key)
    {
        self::checkREDIS();
        return $this->REDIS->exists($key);
    }
    public function Redis_keys($key) {
        self::checkREDIS();
        return $this->REDIS->keys($key);
    }
    
    

    public function uploadFile($fileField){
        self::checkUPLOAD();
        return $this->UPLOAD->upload($fileField);
    }
    public function imageThumb($upfilename,$width,$height,$prefix){
        self::checkUPLOAD();
        return $this->UPLOAD->thumb($upfilename, $width, $height, $prefix);
    }
    
    public function getFileName(){
        self::checkUPLOAD();
        return $this->UPLOAD->getFileName();
    }
    public function getFilePath(){
        self::checkUPLOAD();
        return $this->UPLOAD->getFilePath();
    }
    public function getFileErrorMsg(){
        self::checkUPLOAD();
        // 20140704 wangtianbao 
        //return $this->UPLOAD->getFileErrorMsg();
        return $this->UPLOAD->getErrorMsg();
    }
    public function setFileOption($key, $val){
        self::checkUPLOAD();
        return $this->UPLOAD->set($key,$val);

    }





    public function curl_get($url,$rea_array){
        self::checkCURL();
        return $this->CURL->get($url,$rea_array);
    }
    public function curl_post($url,$rea_array){
        self::checkCURL();
        return $this->CURL->post($url,$rea_array);
    }
    public function curl_upload($url,$rea_array,$files){
        self::checkCURL();
        return $this->CURL->upload($url,$rea_array,$files);
    }
    public function curl_download_img($url,$file_path=''){
        self::checkCURL();
        return $this->CURL->download_img($url,$file_path);
    }

    
    /**
     * 发送邮件  20140704 wangtianbao
     * @param string $to_mail
     * @param string $body
     * @param string $attachment
     * @param string $send_type    //用什么方式发送邮件  smtp pop 等
     * @return bool
     */
    public function _send_mail($to_mail,$body,$attachment,$send_type='def')
    {   
        if(empty($send_type) || $send_type == 'def')
        {
            return $this->MAIL->smtp_send_mail($to_mail,$body,$attachment);
        }
    }
    


    private function checkCURL(){
        if(!$this->CURL){
            throw new FWException('CURL is OFF','500.3');
        }
    }
    private function checkREDIS(){
        if(!$this->REDIS){
            throw new FWException('REDIS is OFF','500.3');
        }
    }
    private function checkUPLOAD(){
        if(!$this->UPLOAD){
            throw new FWException('UPLOAD is OFF','500.3');
        }
    }
    private function checkIMAGE(){
        if(!$this->IMAGE){
            throw new FWException('IMAGE is OFF','500.3');
        }
    }
    
    
    //20140704 wangtianbao
    private function checkMAIL()
    {
        if(!$this->MAIL)
        {
            throw new FWException('MAIL is OFF','500.3');
        }
    }


    /**
    * upload iphone头像   20140704 wangtianbao 
    * @param string $file
    * @param string $type
    * @return boolean|string
    */
    public function upload_iphone_ico_file($file,$type)
    {     
        //如果图片为空则返回错误
        if(empty($file))
        {
            return false;
        }
        if(empty($type))
        {
            $type = 'jpg';
        }
        $config = $this->conf;
        //获取图片上传的配置文件
        $upload_type = $config['UPLOAD']['ALLOW_TYPE'];
        $save_fifle_path = $config['UPLOAD']['FILE_PATH'];
        $max_size = $config['UPLOAD']['MAXSIZE']; 
        
        //20140711 wangtianbao  判断图片的路径
        if (!file_exists($save_fifle_path) || !is_writable($save_fifle_path)) {
            if (@!mkdir($save_fifle_path, 0755,true)) {
                //存储图片的路径
                return false;
            }
        }
        if(!in_array($type, $upload_type))
        {  //扩展名不是指定的类型
           return false;
        }
        $src = $file;
        //随机文件名
        $name = date('YmdHis') . "_" . rand(100, 999) . '.' . $type;
        $picdir = $save_fifle_path;
        $filename = $picdir . '/' . $name;
        if (file_put_contents($filename, $src))
        {   
            $file_size = filesize($filename);
            
            if($file_size > $max_size)
            {   //超过规定的大小
                unlink($filename);
                return false;
            }
            return $name;
        }
        else
        {
            return false;
        }
        
        
    }

    /**
     * 发送消息到MQTT
     * @param string $msg 消息
     * @param string $topic 主题
     * @return boolean
     */
    public function SAM_send($msg, $topic) {
        if (!$this->SAM) {
            throw new FWException('SAM is OFF', '500.3');
        }

        if (is_array($msg)) {
            $msg = json_encode(_totext($msg));
        }

        $top = $this->conf['SAM']['topic'] . $topic;
        $msg = new SAMMessage($msg);
        $rst = $this->SAM->send($top, $msg);
        if (!$rst) {
            throw new FWException($this->SAM->error, $this->SAM->errno);
        }
        return $rst;
    }

    /**
     * 发送消息到队列
     * @param string $msg 消息
     * @param string $queue 队列别名
     * @return boolean
     */
    public function MCC_send($msg, $queue) {
        if (!$this->MCC) {
            throw new FWException('MCC is OFF', '500.3');
        }

        if (is_array($msg)) {
            $msg = json_encode(_totext($msg));
        }

        try {
            return $this->MCC->send($msg, $queue);
        } catch (Exception $ex) {
            if ($ex->getCode() == 203003) {
                return false;
            } else {
                throw $ex;
            }
        }
    }

}
