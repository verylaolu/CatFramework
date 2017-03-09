<?php

function fun_tomcc($queue, $priorityLevel=4)
{
	
	$file_name = "reftomcc.debug"; 
	$file_pointer = fopen($file_name, "a"); 
	
		$host = "112.126.65.236";
	  $port = 20066;
	  $proxy = new MccProxy($host,$port,$queue); 
	  //autoid	userid	username	pwd	isusername	refid	refcnt	refcmtnum	whickcmt	tasktime	prioritylevel
	  if (empty($info[0]["othertasktype"]))
	  {
	  	$info[0]["othertasktype"] = "ref";
	  }
	  $msg =  "fun test ||"." toMcc";
	  fwrite($file_pointer, $msg."\n");
	  $message = $proxy->send_message($msg,$queue,$priorityLevel,$expiration=false);
	
	fclose($file_pointer); 
}
?>
