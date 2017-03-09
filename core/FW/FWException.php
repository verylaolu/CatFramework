<?php
/**
 * FW 异常处理累
 * @category   FW
 * @package  framework
 * @subpackage  core/FW
 * @author    陆春宇
 */
class FWException extends Exception{
    public $conf;
    function __construct($msg,$code){
        parent::__construct($msg,$code);
        $this->conf = getConf();
        $log = P(LOG,$this->conf['LOG']);
//        $errorMsg = 'Error on line '.$this->getLine().' in '.$this->getFile()
//            .': '.$this->getMessage().' ';
        $errorMsg = 'Error is'.': '.$this->getMessage().' ';
        if($log){
            return $log->setException($code,$errorMsg);
        }else{
            _return(510,array('exception_code'=>$code,'exception_msg'=>$errorMsg));
        }
        
    }

}
