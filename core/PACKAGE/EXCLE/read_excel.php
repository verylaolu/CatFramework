<?php

/**
 * 导入excel
 */



function read_data_from_excle($excle_path){
    header('Content-Type:text/html; charset=utf-8');
    require_once 'reader.php';
    $data = new Spreadsheet_Excel_Reader();
    $data->setOutputEncoding('utf-8');
    //$data->read('/Users/tb/Sites/test_data/test.xls');
    $data->read($excle_path);
    $rst = $data->getExcelResult();
    $insert_data = array();

    foreach ($rst as $k => $v) {
        if ($k == 1 || $k == 2) {
            continue;
        }
        $insert_data[] = $v;
    }
    return $insert_data;
}


