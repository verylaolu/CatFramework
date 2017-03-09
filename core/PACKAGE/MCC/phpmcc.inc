<?php

/**
 * mcc 消息实体
 */
class XcpMessage{
	var $sid;
	var $cid;
	var $action;
	var $dest;
	var $msgclass;
	var $header;
	function XcpMessage($action,$queue,$content=false){
		$this->_init();
		switch(strtolower(trim($action))){
			case "send":
				$this->action="send";
				break;
			case "rec":
				$this->action="rec";
				break;
			default:
				$this->_err=-1;
				$this->_errmsg="未知的action类型[$action]";
				break;
		}
		$this->dest=$queue;
		$this->content=$content;
	}
	/**
	 * 输出xml
	 */
	function renderxml(){
		switch($this->action){
			case "send":
				return $this->_render_send_xml();
			case "rec":
				return $this->_render_rec_xml();
			default:
				$this->_err=-1;
				$this->_errmsg="未知的action类型[$action]";
				return false;
		}
	}
	function setExpiration($expiration){
		if($expiration!=false)
			$this->header["jmsExpiration"]=$expiration;
	}
	function setPriority($priority){
		if($priority!=false)
			$this->header["jmsPriority"]=$priority;
	}
	
	function _init(){
		$this->sid="166892229364536596890@12040168192";
		$this->cid="132145747051395077120@12040168192@0";
		$this->action=false;
		$this->dest=false;
		$this->msgclass="text";
		$this->header=array(
			"jmsDeliveryMode"=>"PERSISTENT"
			,"jmsRedelivered"=>"false"
			,"jmsDestination"=>"queue:sendwbmsg"
			,"jmsExpiration"=>0
			,"jmsMessageID"=>"ID:166892229364536596890@12040168192"
			,"jmsTimestamp"=>"1366683734595"
			,"jmsPriority"=>2
			,"jmsType"=>"org.mom4j.jms.TextMessageImpl"
		);
		$this->content=false;
	}
	function _render_send_xml(){
		$hxml=false;
		if($this->header){
			foreach($this->header as $k=>$v){
				$hxml.=sprintf(' %s="%s"',$k,htmlentities($v));
			}
		}
		//echo $this->content."   \n\n\n";
			$file_name = "phpmcc.debug"; 
    	$file_pointer = fopen($file_name, "a"); 
    	fwrite($file_pointer, "content=".$this->content." \n");
			fclose($file_pointer); 
			
		$xml=sprintf(
'<?xml version="1.0" ?><xcpmsg sid="%s" action="%s" dest="%s" msgclass="%s" >%s<body><content><![CDATA[%s]]></content></body></xcpmsg>'
		,htmlentities($this->sid),htmlentities($this->action)
		,htmlentities($this->dest),htmlentities($this->msgclass)
		,$hxml?sprintf('<header %s />',$hxml):""
		,$this->content);
		
		return $xml."\n";
	}
	function _render_rec_xml(){
	 	$xml=sprintf(
'<?xml version="1.0" ?><xcpmsg sid="%s" action="%s" dest="%s" cid="%s" ></xcpmsg>',
		$this->sid,$this->action,$this->dest,$this->cid);
		return $xml."\n";
	}
	
}
/**
 * 消息中心代理
 */
class MccProxy{
    
    var $host=false;
    var $port=false;
    
    // 指向socket的引用   
    var $_socket=false;
    // 用于记录错误编号
    var $_err=false;
    // 记录错误消息
    var $_errmsg=false;
    var $_debug=false;
    
    function MccProxy($host,$port,$queue=false){
    	$this->_init();
    //	$this->_debug=true;
    	if (!$this->connect($host,$port))
	{
            throw new Exception("mcc server is down\n", 203003);
		echo "mcc server is down\n";
		exit;
	}
    	if($queue)
    		$this->queue=$queue;
    }
    /**
     * 发送消息到消息中心
     */
    function send_message($msg,$queue=false,$priority=false,$expiration=false){
    	
    	if(!$queue){
    		$queue=$this->queue;
    	}
    	$file_name = "phpmcc.debug"; 
    	$file_pointer = fopen($file_name, "a"); 
    	//fwrite($file_pointer, "send_message queue=".$queue."\n");
			fclose($file_pointer); 
			
    	$xcpmsg=new XcpMessage("send",$queue,$msg);
    	$xcpmsg->setExpiration($expiration);
    	$xcpmsg->setPriority($priority);
    	$recmsg=$xcpmsg->renderxml();
    	
    	if($this->_write(trim($recmsg))){
    		if($msg=$this->_read()){
    			return $this->parsemsg($msg)=="OK";
    		}
    	}
    	return false;
    }
    /**
     * 获取消息从消息中心
     */
    function get_message($queue=false){
    	if(false==$queue){
    		$queue=$this->queue;
    	}
    	$xcpmsg=new XcpMessage("rec",$queue);
    	$recmsg=$xcpmsg->renderxml();

    	if($this->_write($recmsg)){
    		if($msg=$this->_read()){
    			return $this->parsemsg($msg);
    		}
    	}

    	return false;
    }
    function parsemsg($msg){
    	if(false!==$f=strpos($msg,"<error")){
    		if(false!==$s=strpos($msg,">",$f)){
    			if(false!==$e=strpos($msg,"</error>",$s)){
    				$this->_err=-5;
    				$this->_errmsg=substr($msg,$s+1,($e-$s-1));
    				$this->_d($this->_errmsg);
    				return false;
    			}
    		}
    		$this->_err=-6;
    		$this->_errmsg="远程处理时发生未知异常,$msg";
    		return false;
    	}else if(false!==$f=strpos($msg,"<xcpmsg")){
    		if(false!==$f=strpos($msg,"<content")){
	    		if(false!==$s=strpos($msg,"<![CDATA[",$f)){
	    			if(false!==$e=strpos($msg,"]]>",$s)){
	    				$recmsg=substr($msg,$s+9,($e-$s-9));
	    				return $recmsg;
	    			}
	    		}
	    		$this->_err=-7;
	    		$this->_errmsg="没有发现正常的返回消息,$msg";
    		}else{
    			return NULL;
    		}
    		return false;
		}else if(false!==$f=strpos($msg,"<xcp>")){
			$s=$f+5;
			if(false!==$e=strpos($msg,"</xcp>",$s)){
				$recmsg=substr($msg,$s,($e-$s));
				return $recmsg;
			}
    		$this->_err=-7;
    		$this->_errmsg="没有发现正常的返回消息,$msg";
    		return false;
		}else{
    		$this->_err=-8;
    		$this->_errmsg="未识别的返回消息,$msg";
    		return $msg;
    	}
    }
    /**
     * 清除现有环境
     * 关闭可能存在的连接
     */
    function clear(){
    	$this->close();
    	$this->_init();
    }
    /**
     * 关闭现有连接
     */
    function close(){
    	if($this->_socket){
    		socket_close($this->_socket);
    		$this->_socket=false;
    		return true;
    	}
    	return false;
    }
    /**
     * 建立到指定服务器的连接
     * 参数
     *  $host:主机名，域名或ip地址
     *  $port:端口号
     */
    function connect($host=false,$port=false){
    	if($host==false){
    		$host=$this->host;
    	}else{
    		$host=trim($host);
    	}
    	if($port==false){
    		$port=$this->port;
    	}else{
    		$port=intval($port);
    	}
    	$hstr=join("",explode(".",$host));
    	if($hstr==$host=gethostbyname($host)){
    		$this->_err=-1; 
    		$this->_errmsg="不识别的主机名";
    		return false; 
    	}
    	$this->host=$host;
    	$this->port=$port;
        $this->_socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        $this->_d("connect to %s:%s",$host,$port);
        $result =  socket_connect($this->_socket,$host,$port);
    	if ($result <= 0){
    		$this->_err=-2;
    		$this->_errmsg=sprintf("Error:建立连接过程中出现异常!
   socket result [%s:%s].",$result,socket_strerror($result));
   			$this->_d($this->_errmsg);
   			return false;
    	}
    	return true;
    }
    
    function _write($msg){
        if(!socket_write($this->_socket,$msg,strlen($msg))){
        	$this->_err=-3;
        	$ec=socket_last_error();
        	$this->_errmsg=sprintf("Error:向远程连接发送数据时发生异常!
   socket result [%s:%s].",$ec,socket_strerror($ec));
   		$this->connect();
   		sleep(3);
        	return false;
        }


        return true;
       // return $r;
    }
    function _read($length=1024){
        if(false===$r=socket_read($this->_socket,$length)){
        	$this->_err=-4;
        	$ec=socket_last_error();
        	$this->_errmsg=sprintf("Error:从远程连接接收数据时发生异常!
   socket result [%s:%s].",$ec,socket_strerror($ec));
        	return false;
        }
        return $r;
    	
    }
    
    /**
     * 初始化
     */
    function _init(){
    	// 设置默认值
    	$this->host="127.0.0.1"; 
    	$this->port=20066;
    	$this->_err=false;
    	$this->_errmsg=false;
    	$this->_socket=false;
    	$this->_debug=false;
    }
    function enable_debug(){
    	$this->_set_debug();
    }
    function disable_debug(){
    	$this->_set_debug(false);
    }
    function _set_debug($debug=true){
    	$this->_debug=$debug==true;
    }
    
    
	/**
	 * 输出调试信息
	 */
	function _d($val=""){
		if(!$this->_debug)
			return false;
		
		$args=func_get_args();
		if(function_exists("_d")){
			return call_user_func_array('_tostring',$args);
		}
		$traces=debug_backtrace();
		$msg=call_user_func_array(array($this,'_tostring'),$args);
	    printf("DEBUG:%s %s %s %s\n"
	    	,$traces[0]['file'],$traces[0]['line'],$traces[1]?$traces[1]['function']:""
	    	,$msg?sprintf("%s\n",$msg):""
	    );
	    
	    return true;
	}
	/**
	 * 将变量转换成可以输出的字符串
	 */
	function _tostring($val=""){
		$args=func_get_args();
		if (is_array($val) || is_object($val) || is_resource($val)) {
	        $msg=sprintf("%s",print_r($val,true));
	    } else if(is_string($val)){
	    	if(func_num_args()>1){
	    		$msg=call_user_func_array('sprintf',array_map(array($this,'_tostring'),$args));
	    	}else
	    		$msg=$val;
	    } else{
	    	ob_start();
	    	var_dump($val);
	    	$msg=ob_get_contents();
	    	ob_end_clean(); 
	    }
	    return $msg;
	}
}

abstract class messageProcessor{
    abstract protected function onMessage($message);
}

class MccListener{
	var $messageProcessor;
	var $proxy;
	
	function MccListener($host,$port,$queue,$messageProcessor){
		$this->proxy=new MccProxy($host,$port,$queue);
		$this->messageProcessor=$messageProcessor;
	}
	function listen(){
		while(true){

			$message = $this->proxy->get_message();

			if ( ! is_null($message)){
				$r=$this->messageProcessor->onMessage($message);
			} else { //wait
				usleep(3);
			}
		}
	}
}

?>
