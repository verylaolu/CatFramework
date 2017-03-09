<?php
/**
 * 
 * 生成Pdf类
 * 
 * 
 */
class createPdf {

    public $PDF = null;
    
    private $conf = null;

    
    public function __construct($conf) { 
        //创建生成Pdf的目录
        $this->conf = $conf;
        $this->createFilePath();
        
        //pdf类
        require_once 'MPDF/mpdf.php';
        $this->PDF = new mPDF();
    }
    
    
    
    
    /**
     * 生成Pdf文件
     * @param type $content
     * @param type $file_name
     */
    public function createPdfFile($content,$file_name){
        
        if(empty($content)){
            exit("参数错误:createPdfFile的content不能为空");
        }
        
        if(empty($file_name)){
            exit("参数错误：createPdfFile的file_name(文件名)不能为空!");
        }
        
       
        $file_name = $this->conf['UPLOAD']['PDF_PATH'] . '/' .$file_name;
        
        $mpdf = $this->PDF;
        $mpdf->useAdobeCJK = true;
        $mpdf->SetAutoFont(AUTOFONT_ALL);
        $mpdf->SetDisplayMode('fullpage');
        if(is_array($content)){
            $cnt = count($content);
            foreach($content as $k=>$v){
                $mpdf->WriteHTML($v);
                if($k==($cnt-1)){
                    break;
                }
                $mpdf->AddPage();
            }
        }else{
            $mpdf->WriteHTML($content);
        }
        $mpdf->Output($file_name,'F');
        return $file_name;
    }
    
    
    /**
     * 创建存储Pdf的路径
     * @param type $path
     */
    private function createFilePath(){      
        $path = $this->conf['UPLOAD']['PDF_PATH'];
        if (!file_exists($path) || !is_writable($path)) {
            if (@!mkdir($path, 0755, true)) {
                exit('文件创建失败');
            }
        }
    }
    
    
 
    
    

}
