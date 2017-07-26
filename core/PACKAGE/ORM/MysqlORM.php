<?php

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
        $this->_DB               = $db;
        $this->_DB_CONF          = $_DB_Conf;
        global $app_path;
        $this->_OBJ_PATH         = $app_path . '/object/';
        $this->_MODEL_PATH       = $app_path . '/model/';
        $this->_ACTION_PATH      = $app_path . '/action/';
        $this->_CONTROLLERS_PATH = $app_path . '/controllers/';
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

    /**
     * [MAKE_ORM_OBJ_CODE 创建OBJ]
     */
    public function MAKE_ORM_OBJ_CODE() {
        if (!is_dir($this->_OBJ_PATH)) {
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
        echo $table_name . '<br>';
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
        $str.= " * ORM_OBJ_代码生成\r\n";
        $str.= " * @category   ORM_OBJ\r\n";
        $str.= " * @subpackage platform/obj\r\n";
        $str.= " * @author ORM_OBJ\r\n";
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
        chmod("{$this->_OBJ_PATH}{$class_name}Object.php", 0777);
    }

    /**
     * [MAKE_ORM_MODEL_CODE 创建MODEL]
     */
    public function MAKE_ORM_MODEL_CODE() {
        if (!is_dir($this->_MODEL_PATH)) {
            mkdir($this->_MODEL_PATH, 0777);
        }

        $info = $this->_DB->fetchAll("show tables;");
        foreach ($info as $key => $value) {
            $info[$key]["TABLE_NAME"] = $value[key($value)];
            $this->FILE_PUT_ORM_MODEL_CODE($info[$key]);
        }
        $this->FILE_PUT_ORM_BASE_MODEL_CODE();
    }

    protected function FILE_PUT_ORM_MODEL_CODE($info) {
        $title      = explode('_', $info['TABLE_NAME']);
        $class_name = '';
        foreach ($title as $key => $value) {
            $class_name.=ucfirst($value);
        }
        $str.= "<?php\r\n";
        $str.= "/**\r\n";
        $str.= " * ORM_MODEL_代码生成\r\n";
        $str.= " * @category   ORM_MODEL\r\n";
        $str.= " * @subpackage platform/model\r\n";
        $str.= " * @author ORM_MODEL\r\n";
        $str.= " */\r\n";
        $str.= "class {$class_name}Model extends BaseModel{\r\n";
        $str.= '    public $table="' . $info['TABLE_NAME'] . '"' . ";\r\n";
        $str.= '    public function __construct($REG_PACKAGE = array()) {' . "\r\n";
        $str.= '        parent::__construct($REG_PACKAGE);' . "\r\n";
        $str.= '    }' . "\r\n";
        $str.= '}';
        file_put_contents("{$this->_MODEL_PATH}{$class_name}Model.php", $str);
        chmod("{$this->_MODEL_PATH}{$class_name}Model.php", 0777);
    }

    protected function FILE_PUT_ORM_BASE_MODEL_CODE() {
        $str.= "<?php\r\n";
        $str.= "/**\r\n";
        $str.= " * ORM_MODEL_代码生成\r\n";
        $str.= " * @category   ORM_MODEL\r\n";
        $str.= " * @subpackage platform/model\r\n";
        $str.= " * @author ORM_MODEL\r\n";
        $str.= " */\r\n";
        $str.= "class BaseModel extends Model{\r\n";
        $str.= '    public function __construct($REG_PACKAGE = array()) {' . "\r\n";
        $str.= '        parent::__construct($REG_PACKAGE);' . "\r\n";
        $str.= "    }\r\n";
        $str.= "}";
        file_put_contents("{$this->_MODEL_PATH}BaseModel.php", $str);
        chmod("{$this->_MODEL_PATH}BaseModel.php", 0777);
    }

    /**
     * [MAKE_ORM_ACTION_SERVER_CODE 创建server_ACTION]
     */
    public function MAKE_ORM_ACTION_SERVER_CODE() {
        if (!is_dir($this->_ACTION_PATH)) {
            mkdir($this->_ACTION_PATH, 0777);
        }

        $info = $this->_DB->fetchAll("show tables;");
        foreach ($info as $key => $value) {
            $info[$key]["TABLE_NAME"] = $value[key($value)];
            $this->FILE_PUT_ORM_ACTION_SERVER_CODE($info[$key]);
        }
        $this->FILE_PUT_ORM_BASE_ACTION_SERVER_CODE();
    }

    protected function FILE_PUT_ORM_ACTION_SERVER_CODE($info) {
        $title      = explode('_', $info['TABLE_NAME']);
        $class_name = '';
        foreach ($title as $key => $value) {
            $class_name.=ucfirst($value);
        }

        $str.= "<?php\r\n";
        $str.= "/**\r\n";
        $str.= " * ORM_ACTION_SERVER_代码生成\r\n";
        $str.= " * @category   ORM_ACTION_SERVER\r\n";
        $str.= " * @subpackage platform/action_server\r\n";
        $str.= " * @author ORM_ACTION_SERVER\r\n";
        $str.= " */\r\n";
        $str.= "class {$class_name}Action extends BaseAction{\r\n";

        $str.= "    public function __construct() {\r\n";
        $str.= '        $this->MODEL = new ' . $class_name . 'Model();' . "\r\n";
        $str.= '        parent::__construct(array("CURL" => true));' . "\r\n";
        $str.= "    }\r\n";
        $str.= "}";
        file_put_contents("{$this->_ACTION_PATH}{$class_name}Action.php", $str);
        chmod("{$this->_ACTION_PATH}{$class_name}Action.php", 0777);
    }

    protected function FILE_PUT_ORM_BASE_ACTION_SERVER_CODE() {

        $str.= "<?php\r\n";
        $str.= "/**\r\n";
        $str.= " * ORM_ACTION_SERVER_代码生成\r\n";
        $str.= " * @category   ORM_ACTION_SERVER\r\n";
        $str.= " * @subpackage platform/action_server\r\n";
        $str.= " * @author ORM_ACTION_SERVER\r\n";
        $str.= " */\r\n";
        $str .= 'class BaseAction extends Action {' . "\r\n";

        $str .= '    public $debug = 0;' . "\r\n";
        $str .= '    public $MODEL;' . "\r\n";

        $str .= '    function __construct($REG_PACKAGE = array()) {' . "\r\n";
        $str .= '        parent::__construct($REG_PACKAGE);' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    function post($url, $data) {' . "\r\n";
        $str .= '        $res = $this->CURL->post($url, $data);' . "\r\n";
        $str .= '        if ($this->debug) {' . "\r\n";
        $str .= '            echo "<span style=\"color:red\">" . $url . "</span><br>:" . $this->CURL->string . "<br><br>";' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '        return $res;' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function add($info) {' . "\r\n";
        $str .= '        return $this->MODEL->_insert($info);' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function set($condition, $info) {' . "\r\n";
        $str .= '        return $this->MODEL->_update($condition, $info);' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function set_plus($condition, $info) {' . "\r\n";
        $str .= '       return $this->MODEL->_update_plus($condition, $info);' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function del($condition) {' . "\r\n";
        $str .= '        return $this->MODEL->_delete($condition);' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function get($condition) {' . "\r\n";
        $str .= '        return $this->MODEL->_selectOnce($condition);' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function all($page, $num = 20, $condition = "",$order=" ORDER BY id ASC") {' . "\r\n";
        $str .= '       ' . "\r\n";
        $str .= '        $start = ($page - 1) * $num;' . "\r\n";
        $str .= '        $start = $start <= 0 ? 0 : $start;' . "\r\n";
        $str .= '        return $this->MODEL->_selectList($start, $num, $condition,$order);' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function count($condition = "") {' . "\r\n";
        $str .= '        return $this->MODEL->_count($condition);' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '}' . "\r\n";

        file_put_contents("{$this->_ACTION_PATH}BaseAction.php", $str);
        chmod("{$this->_ACTION_PATH}BaseAction.php", 0777);
    }

    /**
     * [MAKE_ORM_ACTION_CLIENT_CODE 创建CLIENT_ACTION]
     */
    public function MAKE_ORM_ACTION_CLIENT_CODE() {
        if (!is_dir($this->_ACTION_PATH)) {
            mkdir($this->_ACTION_PATH, 0777);
        }

        $info = $this->_DB->fetchAll("show tables;");
        foreach ($info as $key => $value) {
            $info[$key]["TABLE_NAME"] = $value[key($value)];
            $this->FILE_PUT_ORM_ACTION_CLIENT_CODE($info[$key]);
        }
        $this->FILE_PUT_ORM_BASE_ACTION_CLIENT_CODE();
    }

    protected function FILE_PUT_ORM_ACTION_CLIENT_CODE($info) {
        $title      = explode('_', $info['TABLE_NAME']);
        $class_name = '';
        foreach ($title as $key => $value) {
            $class_name.=ucfirst($value);
        }

        $str.= "<?php\r\n";
        $str.= "/**\r\n";
        $str.= " * ORM_ACTION_CLIENT_代码生成\r\n";
        $str.= " * @category   ORM_ACTION_CLIENT\r\n";
        $str.= " * @subpackage platform/action_client\r\n";
        $str.= " * @author ORM_ACTION_CLIENT\r\n";
        $str.= " */\r\n";
        $str .= 'class ' . $class_name . 'Action extends BaseAction {' . "\r\n";
        $str .= '    public function __construct() {' . "\r\n";
        $str .= '        $this->API_MODEL = "' . strtolower($class_name) . '";' . "\r\n";
        $str .= '        parent::__construct(array("CURL" => true));' . "\r\n";
        $str .= '    }' . "\r\n";
        $str .= '}' . "\r\n";
        file_put_contents("{$this->_ACTION_PATH}{$class_name}Action.php", $str);
        chmod("{$this->_ACTION_PATH}{$class_name}Action.php", 0777);
    }

    protected function FILE_PUT_ORM_BASE_ACTION_CLIENT_CODE() {

        $str.= "<?php\r\n";
        $str.= "/**\r\n";
        $str.= " * ORM_ACTION_CLIENT_代码生成\r\n";
        $str.= " * @category   ORM_ACTION_CLIENT\r\n";
        $str.= " * @subpackage platform/action_client\r\n";
        $str.= " * @author ORM_ACTION_CLIENT\r\n";
        $str.= " */\r\n";
        $str .= 'class BaseAction extends Action {' . "\r\n";

        $str .= '    public $debug = 0;' . "\r\n";
        $str .= '    public $API_MODEL;' . "\r\n";

        $str .= '    function __construct($REG_PACKAGE = array()) {' . "\r\n";
        $str .= '        parent::__construct($REG_PACKAGE);' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    function post($url, $data) {' . "\r\n";
        $str .= '        $res = $this->curl_post($url, $data);' . "\r\n";
        $str .= '        if ($this->debug) {' . "\r\n";
        $str .= '            echo "<span style=\"color:red\">" . $url . "</span><br>:" . $this->CURL->string . "<br><br>";' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '        if($code<400){' . "\r\n";
        $str .= '            return $res["content"];' . "\r\n";
        $str .= '        }else{' . "\r\n";
        $str .= '            return false;' . "\r\n";
        $str .= '        }' . "\r\n";

        $str .= '    }' . "\r\n";
        $str .= '    public function count($data=""){' . "\r\n";
        $str .= '        $res = $this->post(API_URL."/".$this->API_MODEL."/count", $data);' . "\r\n";
        $str .= '        return $res;' . "\r\n";
        $str .= '    }' . "\r\n";
        $str .= '    public function all($page=1,$num=20,$data=""){' . "\r\n";
        $str .= '        $data["page"] = $page;' . "\r\n";
        $str .= '        $data["num"] = $num;' . "\r\n";
        $str .= '        $res = $this->post(API_URL."/".$this->API_MODEL."/all",$data);' . "\r\n";
        $str .= '        return $res;' . "\r\n";
        $str .= '    }' . "\r\n";
        $str .= '    public function get($data=""){' . "\r\n";
        $str .= '        $res = $this->post(API_URL."/".$this->API_MODEL."/get", $data);' . "\r\n";
        $str .= '        return $res;' . "\r\n";
        $str .= '    }' . "\r\n";
        $str .= '    public function add($data=""){' . "\r\n";
        $str .= '        $res = $this->post(API_URL."/".$this->API_MODEL."/add",$data);' . "\r\n";
        $str .= '        return $res;' . "\r\n";
        $str .= '    }' . "\r\n";
        $str .= '    public function set($data="") {' . "\r\n";
        $str .= '        $res = $this->post(API_URL."/".$this->API_MODEL."/set",$data);' . "\r\n";
        $str .= '        return $res;' . "\r\n";
        $str .= '    }' . "\r\n";


        $str .= '    public function set_plus($condition, $info) {' . "\r\n";
        $str .= '       return $this->MODEL->_update_plus($condition, $info);' . "\r\n";
        $str .= '    }' . "\r\n";


        $str .= '    public function del($data="") {' . "\r\n";
        $str .= '        $res = $this->post(API_URL."/".$this->API_MODEL."/del",$data);' . "\r\n";
        $str .= '        return $res;' . "\r\n";
        $str .= '    }' . "\r\n";
        $str .= '}' . "\r\n";


        file_put_contents("{$this->_ACTION_PATH}BaseAction.php", $str);
        chmod("{$this->_ACTION_PATH}BaseAction.php", 0777);
    }

    /**
     * [MAKE_ORM_CONTROLLER_SERVER_CODE 创建server_CONTROLLER]
     */
    public function MAKE_ORM_CONTROLLER_SERVER_CODE($prefix = 'api') {
        if (!is_dir($this->_CONTROLLERS_PATH)) {
            mkdir($this->_CONTROLLERS_PATH, 0777);
        }

        $info = $this->_DB->fetchAll("show tables;");
        foreach ($info as $key => $value) {
            $info[$key]["TABLE_NAME"] = $value[key($value)];
            $this->FILE_PUT_ORM_CONTROLLER_SERVER_CODE($info[$key]);
        }
        $this->FILE_PUT_ORM_BASE_CONTROLLER_SERVER_CODE($prefix);
    }

    protected function FILE_PUT_ORM_CONTROLLER_SERVER_CODE($info) {
        $title      = explode('_', $info['TABLE_NAME']);
        $class_name = '';
        foreach ($title as $key => $value) {
            $class_name.=ucfirst($value);
        }
        $str.= "<?php\r\n";
        $str.= "/**\r\n";
        $str.= " * ORM_CONTROLLER_SERVER_代码生成\r\n";
        $str.= " * @category   ORM_CONTROLLER_SERVER\r\n";
        $str.= " * @subpackage platform/controller_server\r\n";
        $str.= " * @author ORM_CONTROLLER_SERVER\r\n";
        $str.= " */\r\n";
        $str.= "class {$class_name}Controller extends BaseController{\r\n";

        $str.= "    public function __construct() {\r\n";
        $str.= '        $this->ACTION_CLASS = new ' . $class_name . 'Action();' . "\r\n";
        $str.= '        $this->OBJ_NAME="' . $class_name . 'Object";' . "\r\n";
        $str.= "        parent::__construct();\r\n";
        $str.= "    }\r\n";
        $str.= "}";
        file_put_contents("{$this->_CONTROLLERS_PATH}{$class_name}Controller.php", $str);
        chmod("{$this->_CONTROLLERS_PATH}{$class_name}Controller.php", 0777);
    }

    protected function FILE_PUT_ORM_BASE_CONTROLLER_SERVER_CODE($prefix) {
        $str.= "<?php\r\n";
        $str.= "/**\r\n";
        $str.= " * ORM_CONTROLLER_SERVER_代码生成\r\n";
        $str.= " * @category   ORM_CONTROLLER_SERVER\r\n";
        $str.= " * @subpackage platform/controller_server\r\n";
        $str.= " * @author ORM_CONTROLLER_SERVER\r\n";
        $str.= " */\r\n";
        $str .= 'class BaseController extends Controller {' . "\r\n";
        $str .= '    public $ENUM;' . "\r\n";
        $str .= '    public $ACTION_CLASS;' . "\r\n";
        $str .= '    public $OBJ_NAME;' . "\r\n";

        $str .= '    public $LANG;' . "\r\n";
        $str .= '    public $_APP;' . "\r\n";
        $str .= '    public $_MODULE;' . "\r\n";
        $str .= '    public $_ACTION;' . "\r\n";
        $str .= '    public $_PARM;' . "\r\n";
        $str .= '    public $VERIFY;' . "\r\n";

        $str .= '    function __construct($REG_PACKAGE = array("ORM" => true,)) {' . "\r\n";
        $str .= '        global $enum;' . "\r\n";
        $str .= '        $this->ENUM = $enum;' . "\r\n";
        $str .= '        parent::__construct($REG_PACKAGE);' . "\r\n";

        $str .= '        $obj            = Route::getRoute();' . "\r\n";
        $str .= '        $this->_APP     = strtolower($obj->app);' . "\r\n";
        $str .= '        $this->_MODULE  = strtolower($obj->module);' . "\r\n";
        $str .= '        $this->_ACTION  = strtolower($obj->action_url);' . "\r\n";
        $str .= '        $this->_PARM    = $_REQUEST["parm"];' . "\r\n";
        $str .= '        $this->selectlanguage($this->_MODULE);' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    function selectlanguage($lang) {' . "\r\n";
        $str .= '        if ($lang == "en") {' . "\r\n";
        $str .= '            $this->LANG = include_once LANGUAGE_PATH . "en-us.php";' . "\r\n";
        $str .= '        } elseif ($lang == "ft") {' . "\r\n";
        $str .= '            $this->LANG = include_once LANGUAGE_PATH . "zh-tw.php";' . "\r\n";
        $str .= '        } else {' . "\r\n";
        $str .= '            $this->LANG = include_once LANGUAGE_PATH . "zh-cn.php";' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '        $this->assign("lang", $this->LANG);' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function ' . $prefix . '_add() {' . "\r\n";
        $str .= '        $Obj  = new $this->OBJ_NAME($_POST);' . "\r\n";
        $str .= '        $data = $Obj->getArray();' . "\r\n";
        $str .= '        $info = $this->ACTION_CLASS->add($data);' . "\r\n";
        $str .= '        if ($info > 0) {' . "\r\n";
        $str .= '            _return(202, $info);' . "\r\n";
        $str .= '        } else {' . "\r\n";
        $str .= '            _return(512);' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function ' . $prefix . '_set() {' . "\r\n";
        $str .= '        $Obj  = new $this->OBJ_NAME($_POST);' . "\r\n";
        $str .= '        $data = $Obj->getArray();' . "\r\n";
        $str .= '        if ($this->ACTION_CLASS->set(array("id"=>$Obj->id), $data)) {' . "\r\n";
        $str .= '            _return(202, $data);' . "\r\n";
        $str .= '        } else {' . "\r\n";
        $str .= '            _return(502);' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function ' . $prefix . '_del() {' . "\r\n";
        $str .= '        if (empty($_POST["id"])) {' . "\r\n";
        $str .= '            _return(509);' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '        $data = $this->ACTION_CLASS->del(array("id"=>$_POST["id"]));' . "\r\n";
        $str .= '        if ($data) {' . "\r\n";
        $str .= '            _return(201,$data);' . "\r\n";
        $str .= '        } else {' . "\r\n";
        $str .= '            _return(501);' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function ' . $prefix . '_get() {' . "\r\n";
        $str .= '        $condition = $_POST;' . "\r\n";
        $str .= '        if (count($condition)<=0) {' . "\r\n";
        $str .= '            _return(509);' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '        $info = $this->ACTION_CLASS->get($condition);' . "\r\n";
        $str .= '        if ($info) {' . "\r\n";
        $str .= '            _return(204, $info);' . "\r\n";
        $str .= '        } else {' . "\r\n";
        $str .= '            _return(504);' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function ' . $prefix . '_all() {' . "\r\n";
        $str .= '        $page = empty($_POST["page"]) ? $_POST["page"] = 1 : $_POST["page"];' . "\r\n";
        $str .= '        $num  = empty($_POST["num"]) ? $_POST["num"]  = 20 : $_POST["num"];' . "\r\n";
        $str .= '        $order = empty($_POST["order"]) ? $_POST["order"]  = " order by id desc " : $_POST["order"];' . "\r\n";
        $str .= '        $condition = $_POST;' . "\r\n";
        $str .= '        unset($condition["page"]);' . "\r\n";
        $str .= '        unset($condition["num"]);' . "\r\n";
        $str .= '        unset($condition["order"]);' . "\r\n";
        $str .= '        $info     = $this->ACTION_CLASS->all($page, $num,$condition,$order);' . "\r\n";
        $str .= '        if ($info) {' . "\r\n";
        $str .= '            _return(204, $info);' . "\r\n";
        $str .= '        } else {' . "\r\n";
        $str .= '            _return(504);' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function ' . $prefix . '_count() {' . "\r\n";
        $str .= '        $condition = $_POST;' . "\r\n";
        $str .= '        $count = $this->ACTION_CLASS->count($condition);' . "\r\n";
        $str .= '        if ($count) {' . "\r\n";
        $str .= '            _return(204, $count);' . "\r\n";
        $str .= '        } else {' . "\r\n";
        $str .= '            _return(504);' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '}' . "\r\n";

        file_put_contents("{$this->_CONTROLLERS_PATH}BaseController.php", $str);
    }

    /**
     * [MAKE_ORM_CONTROLLER_CLIENT_CODE 创建CLIENT_CONTROLLER]
     */
    public function MAKE_ORM_CONTROLLER_CLIENT_CODE($prefix = 'open', $views = '') {
        if (!is_dir($this->_CONTROLLERS_PATH)) {
            mkdir($this->_CONTROLLERS_PATH, 0777);
        }

        $info = $this->_DB->fetchAll("show tables;");
        foreach ($info as $key => $value) {
            $info[$key]["TABLE_NAME"] = $value[key($value)];
            $this->FILE_PUT_ORM_CONTROLLER_CLIENT_CODE($info[$key]);
        }
        $this->FILE_PUT_ORM_BASE_CONTROLLER_CLIENT_CODE($prefix, $views);
    }

    protected function FILE_PUT_ORM_CONTROLLER_CLIENT_CODE($info) {
        $title      = explode('_', $info['TABLE_NAME']);
        $class_name = '';
        foreach ($title as $key => $value) {
            $class_name.=ucfirst($value);
        }
        $str.= "<?php\r\n";
        $str.= "/**\r\n";
        $str.= " * ORM_CONTROLLER_CLIENT_代码生成\r\n";
        $str.= " * @category   ORM_CONTROLLER_CLIENT\r\n";
        $str.= " * @subpackage platform/controller_client\r\n";
        $str.= " * @author ORM_CONTROLLER_CLIENT\r\n";
        $str.= " */\r\n";
        $str .= 'class ' . $class_name . 'Controller extends BaseController {' . "\r\n";
        $str .= '    public function __construct() {' . "\r\n";
        $str .= '        $this->ACTION_CLASS = new ' . $class_name . 'Action();' . "\r\n";
        $str .= '        $this->OBJ_NAME="' . $class_name . 'Object";' . "\r\n";
        $str .= '        $this->MODEL_NAME   = "' . strtolower($class_name) . '";' . "\r\n";
        $str .= '        parent::__construct();' . "\r\n";
        $str .= '    }' . "\r\n";
        $str .= '}' . "\r\n";
        file_put_contents("{$this->_CONTROLLERS_PATH}{$class_name}Controller.php", $str);
        chmod("{$this->_CONTROLLERS_PATH}{$class_name}Controller.php", 0777);
    }

    protected function FILE_PUT_ORM_BASE_CONTROLLER_CLIENT_CODE($prefix, $views) {
        $str.= "<?php\r\n";
        $str.= "/**\r\n";
        $str.= " * ORM_CONTROLLER_CLIENT_代码生成\r\n";
        $str.= " * @category   ORM_CONTROLLER_CLIENT\r\n";
        $str.= " * @subpackage platform/controller_client\r\n";
        $str.= " * @author ORM_CONTROLLER_CLIENT\r\n";
        $str.= " */\r\n";
        $str .= 'class BaseController extends Controller {' . "\r\n";

        $str .= '    public $ENUM;' . "\r\n";
        $str .= '    public $ACTION_CLASS;' . "\r\n";
        $str .= '    public $OBJ_NAME;' . "\r\n";
        $str .= '    public $MODEL_NAME;' . "\r\n";

        $str .= '    public $LANG;' . "\r\n";
        $str .= '    public $_APP;' . "\r\n";
        $str .= '    public $_MODULE;' . "\r\n";
        $str .= '    public $_ACTION;' . "\r\n";
        $str .= '    public $_PARM;' . "\r\n";
        $str .= '    public $VERIFY;' . "\r\n";

        $str .= '    function __construct($REG_PACKAGE = array()) {' . "\r\n";
        $str .= '        parent::__construct(array("SMARTY" => true,"SESSION" => true,"ORM" => true));' . "\r\n";
        $str .= '        global $enum;' . "\r\n";
        $str .= '        $this->ENUM = $enum;' . "\r\n";
        $str .= '        $this->assign("ENUM", $this->ENUM);' . "\r\n";

        $str .= '        $obj            = Route::getRoute();' . "\r\n";
        $str .= '        $this->_APP     = strtolower($obj->app);' . "\r\n";
        $str .= '        $this->_MODULE  = strtolower($obj->module);' . "\r\n";
        $str .= '        $this->_ACTION  = strtolower($obj->action_url);' . "\r\n";
        $str .= '        $this->_PARM    = $_REQUEST["parm"];' . "\r\n";
        $str .= '        $this->selectlanguage($this->_MODULE);' . "\r\n";


        $str .= '        $this->checkLogin();' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    function selectlanguage($lang) {' . "\r\n";
        $str .= '        if ($lang == "en") {' . "\r\n";
        $str .= '            $this->LANG = include_once LANGUAGE_PATH . "en-us.php";' . "\r\n";
        $str .= '        } elseif ($lang == "ft") {' . "\r\n";
        $str .= '            $this->LANG = include_once LANGUAGE_PATH . "zh-tw.php";' . "\r\n";
        $str .= '        } else {' . "\r\n";
        $str .= '            $this->LANG = include_once LANGUAGE_PATH . "zh-cn.php";' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '        $this->assign("lang", $this->LANG);' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function ' . $prefix . '_set() {' . "\r\n";
        $str .= '        $Obj  = new $this->OBJ_NAME($_POST);' . "\r\n";
        $str .= '        $id = $Obj->get("id");' . "\r\n";
        $str .= '        if ($id > 0) {' . "\r\n";
        $str .= '            $data = $Obj->getInfo();' . "\r\n";
        $str .= '            $state = $this->ACTION_CLASS->set($data);' . "\r\n";
        $str .= '            if ($state) {' . "\r\n";
        $str .= '                ajax_return(array("valid" => "true", "message" => "修改信息成功"));' . "\r\n";
        $str .= '            } else {' . "\r\n";
        $str .= '                ajax_return(array("valid" => "false", "message" => "修改信息失败"));' . "\r\n";
        $str .= '            }' . "\r\n";
        $str .= '        } else {' . "\r\n";
        $str .= '            $data = $Obj->getArray();' . "\r\n";
        $str .= '            $id = $this->ACTION_CLASS->add($data);' . "\r\n";
        $str .= '            if ($id > 0) {' . "\r\n";
        $str .= '                ajax_return(array("valid" => "true", "message" => "创建成功", "data" => array("id" => $id)));' . "\r\n";
        $str .= '            } else {' . "\r\n";
        $str .= '                ajax_return(array("valid" => "false", "message" => "创建失败"));' . "\r\n";
        $str .= '            }' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '    }' . "\r\n";
        $str .= '    public function ' . $prefix . '_get($param) {' . "\r\n";
        $str .= '        $info     = $this->ACTION_CLASS->get($param);' . "\r\n";
        $str .= '        if ($info["id"] > 0) {' . "\r\n";
        $str .= '            ajax_return($user);' . "\r\n";
        $str .= '        } else {' . "\r\n";
        $str .= '            ajax_return(false);' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '    }' . "\r\n";
        $str .= '    public function ' . $prefix . '_check() {' . "\r\n";
        $str .= '        $key = $_GET["key"];' . "\r\n";
        $str .= '        $value = $_GET[$key];' . "\r\n";
        $str .= '        $info     = $this->ACTION_CLASS->get(array($key=>$value));' . "\r\n";
        $str .= '        if ($info["id"] > 0) {' . "\r\n";
        $str .= '            ajax_return(array("valid" => "false", "message" => "该信息已经使用，请更换其他"));' . "\r\n";
        $str .= '        } else {' . "\r\n";
        $str .= '            ajax_return(array("valid" => "true"));' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function ' . $prefix . '_add($enum = "") {' . "\r\n";
        $str .= '        if (!empty($enum)) {' . "\r\n";
        $str .= '            $this->assign(key($enum), $enum[key($enum)]);' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '        $this->display("' . $views . '" . $this->MODEL_NAME . "_edit.html");' . "\r\n";
        $str .= '    }' . "\r\n";
        $str .= '    public function ' . $prefix . '_edit($enum = "") {' . "\r\n";
        $str .= '        $id = intval($_GET["id"]);' . "\r\n";
        $str .= '        if($id<=0){' . "\r\n";
        $str .= '            ' . "\r\n";
        $str .= '            header_go("/".$this->MODEL_NAME."/add");' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '        $info     = $this->ACTION_CLASS->get(array("id"=>$id));' . "\r\n";
        $str .= '        if (!empty($enum)) {' . "\r\n";
        $str .= '            $this->assign(key($enum), $enum[key($enum)]);' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '        $this->assign("info",$info);' . "\r\n";
        $str .= '        $this->display("' . $views . '" . $this->MODEL_NAME . "_edit.html");' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function ' . $prefix . '_list($param_str = "", $replace = array(), $order = "",$pah_total=20) {' . "\r\n";
        $str .= '        $condition          = $_GET;' . "\r\n";
        $str .= '        $page               = intval($_GET["p"]);' . "\r\n";
        $str .= '        unset($condition["p"]);' . "\r\n";
        $str .= '        unset($condition["app"]);' . "\r\n";
        $str .= '        unset($condition["model"]);' . "\r\n";
        $str .= '        unset($condition["action"]);' . "\r\n";
        $str .= '        $condition["order"] = !empty($order) ? $order : "ORDER BY ID DESC";' . "\r\n";
        $str .= '        $list               = $this->ACTION_CLASS->all($page, $pah_total, $condition);' . "\r\n";
        $str .= '        if ($list && count($replace) > 0) {' . "\r\n";
        $str .= '            foreach ($list as $key => $value) {' . "\r\n";
        $str .= '                foreach ($replace as $k => $v) {' . "\r\n";
        $str .= '                    $list[$key][$v["show_title"]] = $v["enum"][$value[$v["replace_title"]]];' . "\r\n";
        $str .= '                }' . "\r\n";
        $str .= '            }' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '        $total = $this->ACTION_CLASS->count($condition);' . "\r\n";
        $str .= '        $total = $total["num"];' . "\r\n";
        $str .= '        $param = !empty($_GET[$param_str]) ? $param_str . "=" . $_GET[$param_str] . "&" : "";' . "\r\n";

        $str .= '        $this->page($total, $pah_total, $page, 5, "/" . $this->MODEL_NAME . "/list?" . $param . "p=");' . "\r\n";
        $str .= '        $page = $this->pageDefault();' . "\r\n";
        $str .= '        $this->assign("page", $page);' . "\r\n";
        $str .= '        $this->assign("list", $list);' . "\r\n";
        $str .= '        $this->display("' . $views . '" . $this->MODEL_NAME . "_list.html");' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function ' . $prefix . '_verity() {' . "\r\n";
        $str .= '        $this->VERIFY = P("VERIFY", array("code_key" => "verity", "width" => 150, "height" => 40, "code_num" => 5, "font_size" => 20));' . "\r\n";
        $str .= '        $this->VERIFY->get_code();' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function check_verity_code($verity_code) {' . "\r\n";
        $str .= '        $this->VERIFY = P("VERIFY", array("code_key" => "verity", "width" => 150, "height" => 40, "code_num" => 5, "font_size" => 20));' . "\r\n";
        $str .= '        return $this->VERIFY->check_code($verity_code);' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '    public function checkLogin() {' . "\r\n";
        $str .= '        return true;' . "\r\n";
        $str .= '        $obj = Route::getRoute();' . "\r\n";
        $str .= '        if (($obj->model == "Sso" && ($obj->action == "open_login" || $obj->action == "open_signin")) || $obj->action == "open_verity") {' . "\r\n";
        $str .= '            return true;' . "\r\n";
        $str .= '        } else {' . "\r\n";
        $str .= '            $session_admininfo = $this->SESSION->GET_SESSION("user");' . "\r\n";
        $str .= '            if (!isset($session_admininfo)) {' . "\r\n";
        $str .= '                //没有用户信息' . "\r\n";
        $str .= '                header_go("/sso/signin");' . "\r\n";
        $str .= '            }' . "\r\n";
        $str .= '            return true;' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '    }' . "\r\n";
        $str .= '    public function ' . $prefix . '_del() {' . "\r\n";
        $str .= '        $id = $_POST["id"];' . "\r\n";
        $str .= '        if (!intval($id)) {' . "\r\n";
        $str .= '            ajax_return(array("valid"=>"false", "message" => "删除参数错误"));' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '        if ($this->ACTION_CLASS->del(array("id"=>$id))) {' . "\r\n";
        $str .= '            ajax_return(array("valid"=>"true", "message" => "删除成功"));' . "\r\n";
        $str .= '        } else {' . "\r\n";
        $str .= '            ajax_return(array("valid"=>"false", "message" => "删除错误"));' . "\r\n";
        $str .= '        }' . "\r\n";
        $str .= '    }' . "\r\n";

        $str .= '}' . "\r\n";

        file_put_contents("{$this->_CONTROLLERS_PATH}BaseController.php", $str);
    }

}
