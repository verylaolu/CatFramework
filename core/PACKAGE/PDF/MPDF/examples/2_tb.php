<?php





//==============================================================
//==============================================================
//==============================================================


$url = "http://tb.tbword.net/test_html/t.html";

$content = file_get_contents($url);


$html= $content;

//echo $content;die;


        
include("../mpdf.php");

$mpdf=new mPDF(); 
//$mpdf=new mPDF('UTF-8','A4','','',15,15,44,15);
$mpdf->useAdobeCJK = true;
$mpdf->SetAutoFont(AUTOFONT_ALL);
$mpdf->SetDisplayMode('fullpage');
$mpdf->WriteHTML($content);
$mpdf->Output();
exit;

//==============================================================
//==============================================================
//==============================================================


