<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 分页工具类
 *
 * @author tb
 */
class _PAGE {

    //put your code here


    public $PAGE ;

    /**
     * @param array $option
     * @param $option['size']    每页显示的条数
     * @param $option['cnt']     总数
     * @param $option['page']    当前页
     * @param $option['sub_page]  
     */
    public function __construct($conf) {
        require_cache((dirname(__FILE__) . '/class.page.php'));
        $this->PAGE = Page::getInstance($conf);
    }

}
