<?php
/**
 * 导出excel
 */

/**
 * 导出excle
 * @param type $fields
 * @param type $list
 * @param type $file_name
 */
function export_excle($fields,$list,$file_name){
    
    if(empty($list)){
        exit('没有要导出的数据');
    }
    
    header('Content-Type:text/html; charset=utf-8');
    require_once 'Writer.php';
    
    //设置excle文件名    
    $date = date('ymdhis');
    
    if(empty($file_name)){
        $file = "liujiaohui_" .$date.'.xls';
    }else{
        $file = $file_name . "_" .$date.'.xls';
    }
    
    if(empty($file)){
        exit('文件名错误');
    }
    
    $e = new Spreadsheet_Excel_Writer();
    $e->send($file);
    $e->setVersion(8);
    $s = &$e->addWorksheet($date);
    $s->setInputEncoding('UTF-8');
    
    //写入字段
    foreach ($fields as $k => $v) {
        $s->write(0, $k, $v);
    }
    
    $i = 1;
    foreach ($list as $data) {
        $d_field = array_keys($data);
        foreach($d_field as $k=>$v){
            $s->write($i, $k, $data[$v]);
        }  
        $i++;
    }
    
    if ($e->close() !== false) {
        echo "EXCEL保存失败!";
    }
}



//function query_sql($sql) {
//    $conn = mysql_pconnect("127.0.0.1", "root", "") or trigger_error(mysql_error(), E_USER_ERROR);
//    mysql_select_db("huiyibang", $conn);
//    mysql_set_charset("utf8", $conn);
//    $res = mysql_query($sql);
//    return $res;
//}
//
//function get_row($res) {
//    $ret = array();
//    while ($arr = mysql_fetch_assoc($res)) {
//        $ret[] = $arr;
//    }
//    return $ret;
//}

//$sql = "select * from a_user_base_info where 1";
//$res = query_sql($sql);
//$list = get_row($res);
//
//$fields = array("id", "名称", "英文名","投递时间");
//$date = date('ymdhis');
//$e = new Spreadsheet_Excel_Writer();
//
//$file = 'test_' . $date . '.xls';
//
//$e->send($file);
//$e->setVersion(8);
//$s = &$e->addWorksheet($date);
//$s->setInputEncoding('UTF-8');
//foreach ($fields as $k => $v) {
//    $s->write(0, $k, $v);
//}
//$i = 1;
//$j = 0;
//foreach ($list as $data) {
//    $s->write($i, 0, $data['user_id']);
//    $s->write($i, 1, $data['name']);
//    $s->write($i, 2, $data['name_eng']);
//    $s->write($i, 3, date('Y-m-d H:i:s', $data['update_time']));
//    $i++;
//}
//if ($e->close() !== false) {
//    echo "EXCEL保存失败!";
//}






