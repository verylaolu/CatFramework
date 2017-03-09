<?php
/**
* Simple example script using PHPMailer with exceptions enabled
* @package phpmailer
* @version $Id$
*/

require '../class.phpmailer.php';
ini_set("display_errors", 0);
function send_mail($frommail,$tomail,$subject,$body,$ccmail=null,$bccmail=null) {  
 $mail = new PHPMailer();  
 $mail->IsSMTP();                            // 经smtp发送  
 $mail->Host     = "smtp.163.com";           // SMTP 服务器  
 $mail->SMTPAuth = true;                     // 打开SMTP 认证  
 $mail->Username = "13581554257@163.com";    // 用户名  
 $mail->Password = "mrq123456";          // 密码  
 $mail->From     = $frommail;                  // 发信人  
 $mail->FromName = "suport of 51yip";        // 发信人别名  
 $mail->AddAddress($tomail);                 // 收信人  
 
 $mail->WordWrap = 50;  
 $mail->IsHTML(true);                            // 以html方式发送  
 $mail->Subject  = $subject;                 // 邮件标题  
 $mail->Body     = $body;                    // 邮件内空  
 $mail->AltBody  =  "请使用HTML方式查看邮件。";  
 return $mail->Send();  
}  
  
$result= send_mail("13581554257@163.com","1667057858@qq.com","test","test" . time());  

var_dump($result);


die;




die;

try {
	$mail = new PHPMailer(true); //New instance, with exceptions enabled

	$mail->IsSMTP();                           // tell the class to use SMTP
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->Port       = 25;                    // set the SMTP server port
	$mail->Host       = "smtp.163.com"; // SMTP server
	$mail->Username   = "13581554257@163.com";     // SMTP server username
	$mail->Password   = "mrq123456";            // SMTP server password

	$mail->IsSendmail();  // tell the class to use Sendmail

	//$mail->AddReplyTo("name@domain.com","First Last");

	$mail->From       = "name@domain.com";
	$mail->FromName   = "First Last";

	$to = "1667057858@qq.com";

	$mail->AddAddress($to);

	$mail->Subject  = "First PHPMailer Message";

	$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
	$mail->WordWrap   = 80; // set word wrap

	$mail->MsgHTML('sssssssssssssssssssssssss');

	$mail->IsHTML(true); // send as HTML

	$mail->Send();
	echo 'Message has been sent.';
} catch (phpmailerException $e) {
	echo $e->errorMessage();
}
