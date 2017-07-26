<?php

class TREE {

    public $info = array();
    private $pid  = '';

    function getTree($pid, $arr, $db_id_str, $db_partent_str, $title, $gettype = '', $state_str = '', $state = null) {

        $this->pid = $this->pid || $this->pid === 0 ? $this->pid : $pid;
        $tree      = array();
        $child     = $this->getChild($pid, $arr, $db_id_str, $db_partent_str, $gettype, $state_str, $state);
        foreach ($child as $k => $v) {
            if (!is_null($state) && $v[$state_str] != $state && $v[$db_id_str] != $this->pid) {
                continue;
            }
            $tree[]    = $v;
            $new_child = $this->getTree($v[$db_id_str], $arr, $db_id_str, $db_partent_str, $title, $gettype, $state_str, $state);
            if ($new_child) {
                if ($gettype) {
                    foreach ($new_child as $nk => $nv) {

                        $nv[$title]          = $nv[$title];
                        $tree [$k]['list'][] = $nv;
                    }
                } else {
                    foreach ($new_child as $nk => $nv) {

                        $nv[$title] = '┗━' . $nv[$title];
                        $tree []    = $nv;
                    }
                }
            }
        }
        return $tree;
    }

    function getChild($id, $arr, $db_id_str, $db_partent_str, $gettype = '', $state_str = '', $state = null) {
        $child = array();
        if ($gettype) {
            foreach ($arr as $k => $v) {
                if ($v[$db_partent_str] == $id) {
                    $v['title'] = $v['title'];
                    $child[]    = $v;
                }
            }
        } else {
            foreach ($arr as $k => $v) {

                if ($v[$db_partent_str] == $id) {
                    $v['title'] = '┗━ ' . $v['title'];
                    $child[]    = $v;
                }
            }
        }

        return $child;
    }

    function getParentTrue($tree_list, $id_str, $parent_str, $start, $end) {
        if($start==$end){
            return $this->info;
        }
        foreach ($tree_list as $key => $value) {
            if ($value[$id_str] == $start) {
                array_unshift($this->info, $value);

                $this->getParentTrue($tree_list, $id_str, $parent_str, $value[$parent_str], $end);
            }
            continue;
        }

    }

}

//$rows = array(   
//    array(    'id' => 1,           'name' => 'dev',             'parentid' => 0       ),   
//    array(    'id' => 2,           'name' => 'php',             'parentid' => 1       ),   
//    array(    'id' => 3,           'name' => 'smarty',          'parentid' => 2       ),   
//    array(    'id' => 4,           'name' => 'life',            'parentid' => 0       ),      
//    array(    'id' => 5,           'name' => 'pdo',             'parentid' => 2       ),   
//    array(    'id' => 6,           'name' => 'pdo-mysql',       'parentid' => 5       ),   
//    array(    'id' => 7,           'name' => 'java',            'parentid' => 1       )   
//); 
//$class = new TREE();
//$aaa = $class->getTree(0,$rows,'id','parentid','name');
//echo '<pre>';
//print_r($aaa);
?>
