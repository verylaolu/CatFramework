<?php
/**
 * FW 发邮件
 * @category   FW
 * @package  framework
 * @subpackage  core/PACKAGE/MAIL
 * @author    陆春宇
 */

class SendMail {
    
     public $mail = null;
     
     public $conf = null;


     public function __construct($conf)
     {  
        require_once  dirname(__FILE__) . '/PHPMailer/class.phpmailer.php';
        $this->mail = new PHPMailer();
        $this->conf = $conf;
     }
     
     
     /**
      * 
      * @param type $to_mail
      * @param type $body
      * @param type $attachment
      * @return type
      */
    public function smtp_send_mail($to_mail, $body, $attachment)
    {
        $config = $this->conf;
        $mail = $this->mail;
        $mail->CharSet ="UTF-8";
        $mail->IsSMTP();                        // 经smtp发送  
        $mail->Host = $config['SMTP']['HOST'];           // SMTP 服务器  
        $mail->SMTPAuth = true;                     // 打开SMTP 认证  
        $mail->Username = $config['SMTP']['USERNAME'];    // 用户名  
        $mail->Password = $config['SMTP']['PASSWORD'];          // 密码  
        $mail->From = $config['SMTP']['FROM'];                  // 发信人  
        $mail->FromName = $config['SMTP']['FROMNAME'];        // 发信人别名   
        
        // Modify by HuangHong, 2014/11/26
        if (!is_array($to_mail)) {
            $to_mail = array($to_mail);
        }
        foreach ( $to_mail as $addr) {
            $mail->AddAddress($addr);                 // 收信人
        }
        
        //$mail->WordWrap = 50;
        $mail->IsHTML(true);                            // 以html方式发送  
        $mail->Subject = $config['SUBJECT'];                 // 邮件标题  
        $mail->Body = $body;                    // 邮件内容  
        if (!empty($attachment))
        {
            $mail->AddAttachment($attachment);
        }
        $mail->AltBody = "请使用HTML方式查看邮件。";
        return $mail->Send();
    }

}