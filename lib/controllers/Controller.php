<?php

/**
 * FW Controller基类：控制器,提供控制器使用的插件功能，被继承后可直接使用（模板引擎）
 * 类包含 验证码，模板引擎，AJAX JSON ARRAY OBJECT
 *
 * @category   FW
 * @package  framework
 * @subpackage  lib/controller
 * @author    陆春宇
 */
abstract class Controller extends FWLibBase {

    protected $SMARTY;
    protected $PAGE;
    protected $_PACKAGE;
    protected  $SMARTY_CACHE_ID=null;

    protected function __construct($REG_PACKAGE = array()) {
        parent::__construct();
        if (isset($REG_PACKAGE['SMARTY'])) {
            $this->SMARTY = $this->SET_PACKAGE('SMARTY');
            unset($REG_PACKAGE['SMARTY']);
        }
        if(is_array($REG_PACKAGE)){
            foreach ($REG_PACKAGE as $key => $value) {
                $this->_PACKAGE[$key] = $this->SET_PACKAGE($key);
            }
        }
    }

    protected function assign($tpl_var, $value = null, $nocache = false) {
        return $this->SMARTY->assign($tpl_var, $value, $nocache);
    }

    protected function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false) {
        $cache_id = isset($cache_id)?$cache_id:$this->SMARTY_CACHE_ID;
        return $this->SMARTY->fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
    }

    protected function display($template = null, $cache_id = null, $compile_id = null, $parent = null) {
        $cache_id = isset($cache_id)?$cache_id:$this->SMARTY_CACHE_ID;
        return $this->SMARTY->display($template, $cache_id, $compile_id, $parent);
    }

    
    protected function clearCache($template_name, $cache_id = null, $compile_id = null, $exp_time = null, $type = null)
    {
        $cache_id = isset($cache_id)?$cache_id:$this->SMARTY_CACHE_ID;
        return $this->SMARTY->clearCache($template_name, $cache_id, $compile_id, $exp_time, $type);

       
    }
    protected function is_cached($tpl_file, $cache_id = null, $compile_id = null)
    {
        $cache_id = isset($cache_id)?$cache_id:$this->SMARTY_CACHE_ID;
        return $this->SMARTY->isCached($tpl_file, $cache_id, $compile_id);
    }
    protected function page($totla, $each_count, $page, $sub_pages = '', $page_link = '', $page_link_suffix='') {
        $conf = array('totla' => $totla, 'each_count' => $each_count, 'page' => $page, 'sub_pages' => $sub_pages, 'page_link' => $page_link,'page_link_suffix'=>$page_link_suffix);
        $this->PAGE = P('PAGE', $conf);
        return;
    }
    protected function pageDefault(){
        if($this->PAGE){
            return $this->PAGE->getDefaultHtml();
        }
        return '';
    }
    protected function getCompletePageArray($page_type = 0){
        if($this->PAGE){
            return $this->PAGE->getCompletePageArray($page_type);
        }
        return '';
    }

//    
}
