<?php

、、
/**
 * Description of MysqlORM
 *
 * @author luchunyu<luchunyu@yuemore.com>
 */
class MysqlORM {

    private static $INSTANCES = array();
    private $_DB;
    private $_DB_CONF;
    private $_OBJ_PATH;

    public function __construct($db, $_DB_Conf) {
        $this->_DB      = $db;
        $this->_DB_CONF = $_DB_Conf;
        global $app_path;
        $this->_OBJ_PATH = $app_path.'/object/';
    }

    /**
     * 获取单例
     * @param string $name 配置名
     * @return Mysql
     */
    public static function getInstance($db, $_DB_Conf, $name = "default") {
        if (array_key_exists($name, self::$INSTANCES)) {
            return self::$INSTANCES[$name];
        }
        $inst                   = new MysqlORM($db, $_DB_Conf);
        self::$INSTANCES[$name] = $inst;
        return $inst;
    }

    public function MAKE_CURD_OBJ_CODE() {
        
    }

    public function MAKE_ORM_OBJ_CODE() {
        if(!is_dir($this->_OBJ_PATH)){
            mkdir($this->_OBJ_PATH, 0777); 
        }
        
        $info = $this->_DB->fetchAll("show tables;");
        foreach ($info as $key => $value) {
            $info[$key]["TABLE_NAME"] = $value[key($value)];
            $info[$key]["FIELD"]      = $this->GET_ORM_TABLE_FIELD($value[key($value)]);
            $this->FILE_PUT_ORM_OBJ_CODE($info[$key]);
        }
    }

    protected function GET_ORM_TABLE_FIELD($table_name) {
        echo $table_name;
        $this->_DB->R_debug = 1;
        $this->_DB->W_debug = 1;
        $data               = $this->_DB->fetchAll("desc `{$table_name}`;");
        $FIELD_TITLE        = array();
        foreach ($data as $key => $value) {
            $FIELD_TITLE[] = $value["Field"];
        }
        return $FIELD_TITLE;
    }

    protected function FILE_PUT_ORM_OBJ_CODE($info) {
        $title      = explode('_', $info['TABLE_NAME']);
        $class_name = '';
        foreach ($title as $key => $value) {
            $class_name.=ucfirst($value);
        }
        $str.= "<?php\r\n";
        $str.= "/**\r\n";
        $str.= " * ORM_BOJ_代码生成\r\n";
        $str.= " * @category   ORM_BOJ\r\n";
        $str.= " * @author ORM_BOJ\r\n";
        $str.= " */\r\n";
        $str.= "class {$class_name}Object extends Object{\r\n";
        foreach ($info['FIELD'] as $key => $value) {
            $str.= "    public \${$value};\r\n";
        }
        $str.= "    public function __construct(\$array = array()) {\r\n";

        foreach ($info['FIELD'] as $key => $value) {
            $str.= "        \$this->{$value}       = isset(\$array['{$value}']) ? \$array['{$value}'] : NULL;\r\n";
        }
        $str.= "    }\r\n";
        $str.= "}";
        file_put_contents("{$this->_OBJ_PATH}{$class_name}Object.php", $str);
    }

}
