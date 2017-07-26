<?php
/**
 * 下载图片至本地
 */
class DownloadImage {

    private $img = '';
    private $filepath = '';
    private $user_agent = 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36';

    function __construct($url, $filepath = '') {
        $this->img = $url;
        if($filepath){
            $this->filepath = $filepath;
        }else{
            $this->filepath = $this->getFilePath();
        }
    }

    function download() {
        $responseHeaders = array();
        $originalfilename = '';
        $ext = '';
        $ch = curl_init($this->img);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $this->getReferer());
        curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgent());
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
        $html = curl_exec($ch);
        $httpinfo = curl_getinfo($ch);
        curl_close($ch);
        if ($html !== false) {
            $httpArr = explode("\r\n\r\n", $html, 2 + $httpinfo['redirect_count']);
            $header = $httpArr[count($httpArr) - 2];
            $body = $httpArr[count($httpArr) - 1];
            $header.="\r\n";
            preg_match_all('/([a-z0-9-_]+):\s*([^\r\n]+)\r\n/i', $header, $matches);
            if (!empty($matches) && count($matches) == 3 && !empty($matches[1]) && !empty($matches[1])) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    if (array_key_exists($i, $matches[2])) {
                        $responseHeaders[$matches[1][$i]] = $matches[2][$i];
                    }
                }
            }
           
            if (0 < preg_match('{(?:[^\/\\\\]+)\.(jpg|jpeg|gif|png|bmp)$}i', $this->img, $matches)) {

                $originalfilename = $matches[0];
                $ext = $matches[1];
            } else {
                if (array_key_exists('Content-Type', $responseHeaders)) {
                    if (0 < preg_match('{image/(\w+)}i', $responseHeaders['Content-Type'], $extmatches)) {
                        $ext = $extmatches[1];
                    }
                }
            }
             
            if (!empty($ext)) {
                $this->filepath .= $this->getFileName() . ".$ext";
             
                $local_file = fopen($this->filepath, 'w+');
                if (false !== $local_file) {
                    if (false !== fwrite($local_file, $body)) {
                        fclose($local_file);
                        $sizeinfo = getimagesize($this->filepath);
                        $imaInfo =  array('filepath' => realpath($this->filepath), 'width' => $sizeinfo[0], 'height' => $sizeinfo[1], 'orginalfilename' => $originalfilename, 'filename' => pathinfo($this->filepath, PATHINFO_BASENAME));
                        return $imaInfo;
                    }
                }
            }
        }
        return false;
    }

    function getFileName() {
        return time();
    }

    function getUserAgent() {
        return $this->user_agent;
    }
    function getFilePath(){     
        $dir = APP_PATH. '/download/'.date('Y').'-'.date('m').'/';
        if (!is_dir($dir))
                mkdir($dir, 0777, true);
        return $dir;
    }
    function getReferer() {
        $regex = "/^(http:\/\/)?([^\/]+)\//i";
        preg_match($regex, $this->img, $matches);
        return $matches[0];
    }

}
