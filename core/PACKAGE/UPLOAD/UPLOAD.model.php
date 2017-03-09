<?php
/**
 * FW UPLOAD包
 * @category   FW
 * @package  framework
 * @subpackage  core/PACKAGE/UPLOAD
 * @author    陆春宇
 */
class _UPLOAD{
    public $UPLOAD;
    function __construct($conf){
        require_cache((dirname(__FILE__) . '/FileUpload.model.php'));
        $this->UPLOAD = new FileUpload($conf);
    }
}