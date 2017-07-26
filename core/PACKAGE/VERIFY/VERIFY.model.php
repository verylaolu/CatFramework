<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 分页工具类
 *
 */
class _VERIFY {


    public $VERIFY ;

    public function __construct($conf) {
        $baseConf = getConf();
        $SESSION = P('SESSION',  $baseConf['SESSION']);
        require_cache((dirname(__FILE__) . '/class.verify.php'));
        $this->VERIFY = new verify($SESSION,$conf['code_key'],$conf['width'],$conf['height'],$conf['code_num'],$conf['font_size']);
    }

}
