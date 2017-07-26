<?php

/**
 * Description of MysqlCURD
 *
 * @author luchunyu<luchunyu@yuemore.com>
 */
class MysqlCURD {

    private static $INSTANCES = array();
    private $_DB;
    private $_SMARTY;
    private $_PAGE;
    private $widget;
    private $table;
    private $url;
    private $primary_key;
    private $permissions= array('c'=>1,'u'=>1,'d'=>1,'r'=>1,'i'=>1);
    private $page_num         = 20;
    private $custom_button;
    private $read_list_where;
    private $read_list_order;

    public function __construct($db, $smarty) {
        $this->_DB     = $db;
        $this->_SMARTY = $smarty;
    }

    /**
     * 获取单例
     * @param string $name 配置名
     * @return Mysql
     */
    public static function getInstance($db, $smarty, $name = 'default') {
        if (array_key_exists($name, self::$INSTANCES)) {
            return self::$INSTANCES[$name];
        }
        $inst                   = new MysqlCURD($db, $smarty);
        self::$INSTANCES[$name] = $inst;
        return $inst;
    }

    public function SET_WIDGET($widget) {
        $this->widget = $widget;
        foreach ($this->widget as $key => $value) {
            if ($value['type'] == 'pk') {
                $this->primary_key = $key;
            }
        }
    }
    public function SET_PERMISSIONS($param) {
        $param['r']=1;
        $this->permissions = $param;
    }
    public function SET_CUSTOM($param) {
        $this->custom_button = $param;
    }
    public function SET_PARAM($key, $value) {
        $this->$key = $value;
        return ture;
    }

    public function SET_URL($url) {
        $this->url = $url;
    }
    public function SET_R_LIST_WHERE($where_str) {
        $this->read_list_where = $where_str;
    }
    public function SET_R_LIST_ORDER($order_str) {
        if(empty($order_str)){
            $this->read_list_order =  " ORDER BY id DESC  , state ASC  ";
        }
        $this->read_list_order = $order_str;
    }

    public function SET_TABLE($table) {
        return $this->table = $table;
    }

    public function TAKE_ACTION($action, $param) {
        $action = empty($action) ? 'r' : $action;
        if (empty($this->table) || empty($this->widget) || empty($this->url)) {
            throw new FWException('CURD param error', '500.1');
        }
        $this->_SMARTY->assign('custom_button',$this->custom_button);
        $this->_SMARTY->assign('permissions', $this->permissions);
        $this->_SMARTY->assign('url', $this->url);
        $this->_SMARTY->assign('primary_key', $this->primary_key);
        if($this->permissions[$action]>0){
            return $this->$action($param);
        }else{
            _return('500', array('primary_key' => $this->primary_key, 'curd_primary_key' => '', 'msg' => '此功能未被授权，不可使用'));
        }
        
    }

    /**
     * 创建
     * @param type $param
     */
    public function c($param) {
        $info;
        $widget = $this->widget;
        foreach ($widget as $key => $value) {

            if (empty($param[$key]) && $value['allow_null'] == false) {
                if ($value['type'] == 'radio' || $value['type'] == 'select') {
                    $param[$key] = 0;
                } else {
                    _return('509', array('primary_key' => $this->primary_key, $this->primary_key => '', 'msg' => $value['title'] . '为必填'));
                }
            }
            if ($value['type'] == 'email') {
                if (!check_is_email($param['email'])) {
                    _return('508', array('primary_key' => $this->primary_key, $this->primary_key => '', 'msg' => $value['title'] . '格式错误'));
                }
            }
            if (!isset($param[$key])) {
                continue;
            }
            if ($value['type'] == 'url') {
                $param[$key] = urlencode($param[$key]);
            }
            if ($value['type'] == 'password') {
                $param[$key] = encryptionPassword($param[$key]);
            }
            /**
             * 添加验证类型
             */
            $info[$key] = $param[$key];
        }
        unset($info[$this->primary_key]);
        $id = $this->create($info);
        if ($id > 0) {
            _return('202', array('primary_key' => $this->primary_key, 'curd_primary_key' => $id, 'msg' => '添加成功'));
        }
    }

    /**
     * 读取
     * @param type $param
     */
    public function r($param) {
        $widget = $this->widget;
        $time_widget;
        $select_widget;
        $radio_widget;
        $url_widget;
        foreach ($widget as $key => $value) {
            if ($value['type'] == 'password' || $value['type'] == 'textarea') {
                unset($widget[$key]);
            }
            if ($value['type'] == 'time') {
                $time_widget[$key] = $value;
            }
            if ($value['type'] == 'select') {
                $select_widget[$key] = $value;
            }
            if ($value['type'] == 'radio') {
                $radio_widget[$key] = $value;
            }
            if ($value['type'] == 'url') {
                $url_widget[$key] = $value;
            }
        }
        $page  = intval($_GET['p']);
        $start = ($page - 1) * $this->page_num;
        $start = $start <= 0 ? 0 : $start;
        $list  = $this->readList($start, $this->page_num);

        $total = $this->countAll();
        $this->page($total, $this->page_num, $page, 5, $this->url . '?curl_action=r&tag&p=');
        $page  = $this->pageDefault();

        foreach ($list as $key => $value) {
            if (is_array($time_widget)) {
                foreach ($time_widget as $k => $v) {
                    $list[$key][$k] = $value[$k] > 0 ? date('Y-m-d H:i', $value[$k]) : '';
                }
            }
            if (is_array($select_widget)) {
                foreach ($select_widget as $k => $v) {
                    $list[$key][$k] = $v['list'][$value[$k]];
                }
            }
            if (is_array($radio_widget)) {
                foreach ($radio_widget as $k => $v) {
                    $list[$key][$k] = $v['list'][$value[$k]];
                }
            }
            if (is_array($url_widget)) {
                foreach ($url_widget as $k => $v) {
                    $list[$key][$k] = urldecode($value[$k]);
                }
            }
        }
        $this->_SMARTY->assign('page', $page);
        $this->_SMARTY->assign('List', $list);
        $this->_SMARTY->assign('widget', $widget);
        return $this->_SMARTY->fetch('LIST.html');
    }

    /**
     * 读取单条
     * @param type $param
     */
    public function i($param) {
        $widget = $this->widget;
        if (!empty($param[$this->primary_key])) {
            $item   = $this->readItem($param[$this->primary_key]);
            $widget = $this->makeWidget($item);
        }
        foreach ($widget as $key => $value) {
            if($value['type']=='url'){
                $widget[$key]['value'] = urldecode($value['value']);
            }
        }

        $this->_SMARTY->assign('primary_key_value', $param[$this->primary_key]);
        $this->_SMARTY->assign('info', $item);
        $this->_SMARTY->assign('widget', $widget);
        return $this->_SMARTY->fetch('ITEM.html');
    }

    /**
     * 更新
     * @param type $param
     */
    public function u($param) {
        $info;
        $widget = $this->widget;
        foreach ($widget as $key => $value) {

            if (empty($param[$key]) && $value['allow_null'] == false) {
                if ($value['type'] == 'radio' || $value['type'] == 'select') {
                    $param[$key] = 0;
                } else {
                    _return('509', array('primary_key' => $this->primary_key, $this->primary_key => '', 'msg' => $value['title'] . '为必填'));
                }
            }
            if ($value['type'] == 'email') {
                if (!check_is_email($param['email'])) {
                    _return('508', array('primary_key' => $this->primary_key, $this->primary_key => '', 'msg' => $value['title'] . '格式错误'));
                }
            }
            if (!isset($param[$key])) {
                continue;
            }
            if ($value['type'] == 'url') {
                $param[$key] = urlencode($param[$key]);
            }
            if ($value['type'] == 'password') {
                $param[$key] = encryptionPassword($param[$key]);
            }
            /**
             * 添加验证类型
             */
            $info[$key] = $param[$key];
        }
        $primary_key_value = $info[$this->primary_key];
        unset($info[$this->primary_key]);
        $id                = $this->update($primary_key_value, $info);
        if ($id > 0) {
            _return('202', array('primary_key' => $this->primary_key, 'curd_primary_key' => $id, 'msg' => '更新数据成功'));
        }
    }

    /**
     * 删除
     * @param type $param
     */
    public function d($param) {
        $primary_key_value = $param['primary_key'];
        if (!isset($primary_key_value)) {
            _return('501', array('primary_key' => $this->primary_key, $this->primary_key => $primary_key_value, 'msg' => '删除条件错误'));
        }
        if ($this->delete($primary_key_value)) {
            _return('201', array('primary_key' => $this->primary_key, 'curd_primary_key' => $id, 'msg' => '删除数据成功'));
        } else {
            _return('501', array('primary_key' => $this->primary_key, $this->primary_key => $primary_key_value, 'msg' => '删除数据失败'));
        }
    }

    private function readList($start = 0, $limit = 20) {
        $sql = "SELECT * FROM $this->table WHERE $this->primary_key > 0 ";
        $sql .= " {$this->read_list_where} ";
        $sql .= " {$this->read_list_order} ";
        
        $sql .= $limit ? "LIMIT $start , $limit" : '';
        return $this->_DB->fetchAll($sql);
    }

    public function delete($id) {
        $sql = "DELETE FROM $this->table WHERE $this->primary_key = $id";
        return $this->_DB->exec($sql);
    }

    private function countAll() {
        $sql   = "SELECT COUNT(*) AS total FROM $this->table WHERE $this->primary_key>0 ";
        $sql .= " {$this->read_list_where} ";
        $total = $this->_DB->fetchOne($sql);
        return $total['total'];
    }

    private function create($info) {
        $state = $this->_DB->insert($this->table, $info);
        if ($state) {
            return $this->_DB->lastInsertId();
        }
    }

    private function update($id, $info) {
        return $this->_DB->update($this->table, $info, array($this->primary_key => $id));
    }

    private function readItem($id) {
        $sql = "SELECT * FROM $this->table WHERE $this->primary_key = $id";
        return $this->_DB->fetchOne($sql);
    }

    private function makeWidget($item) {
        $widget = $this->widget;
        foreach ($item as $key => $value) {
            $widget[$key]['value'] = $value;
        }
        return $widget;
    }

    private function page($totla, $each_count, $page, $sub_pages = '', $page_link = '') {
        $conf        = array('totla' => $totla, 'each_count' => $each_count, 'page' => $page, 'sub_pages' => $sub_pages, 'page_link' => $page_link);
        $this->_PAGE = P('PAGE', $conf);
        return;
    }

    private function pageDefault() {
        if ($this->_PAGE) {
            return $this->_PAGE->getDefaultHtml();
        }
        return '';
    }

}
