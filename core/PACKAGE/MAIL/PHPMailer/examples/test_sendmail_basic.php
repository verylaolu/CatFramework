<html>
<head>
<title>PHPMailer - Sendmail basic test</title>
</head>
<body>

<?php

require_once('../class.phpmailer.php');

$mail             = new PHPMailer(); // defaults to using php "mail()"

$mail->IsSendmail(); // telling the class to use SendMail transport

//$body             = file_get_contents('contents.html');
//$body             = eregi_replace("[\]",'',$body);

$body="hello \n\r yes";

$mail->SetFrom('name@yourdomain.com', 'First Last');

$mail->AddReplyTo("name@yourdomain.com","First Last");

$address = "liuxinmail@gmail.com";
//$address = "liuxinyhoo@yahoo.com.cn";
$mail->AddAddress($address, "John Doe");
//$mail->AddAddress($address1, "John Doe");

$mail->Subject    = "PHPMailer Test Subject via Sendmail, basic";

$mail->isHTML(false);
$mail->Body = $body;


//$mail->AddAttachment("images/phpmailer.gif");      // attachment
//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}

?>

</body>
</html>
