<?php
/**
 * FW MAIL包
 * @category   FW
 * @package  framework
 * @subpackage  core/PACKAGE/MAIL
 * @author    陆春宇
 */
class _MAIL
{
    public $MAIL = null;
    public function __construct($conf)
    {  
        require_once 'SendMail.class.php';
        $this->MAIL = new SendMail($conf);
    }
    
}