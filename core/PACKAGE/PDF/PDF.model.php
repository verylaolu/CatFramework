<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class _PDF {

    public $PDF = null;

    public function __construct($conf) {
        require_once 'createPdf.model.php';
        $conf = getConf();
        $this->PDF = new createPdf($conf);
    }
    


}
