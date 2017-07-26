<?php

/**
 * FW CRUD包
 * @category   FW
 * @package  framework
 * @subpackage  core/PACKAGE/CRUD
 * @author    陆春宇
 * 基于SMARTY    DB使用
 * 使用示例
public function manage() {
    $curd_a_manage = new Curd_a_manageObject();  //示例见下方数组
    $widget        = $curd_a_manage->getInfo();  //示例见下方数组
    $curd          = $this->GET_PACKAGE('CURD');
    $curd->SET_URL("http://" . $_SERVER ['HTTP_HOST'] . '/manage');
    $curd->SET_WIDGET($widget);
    $curd->SET_TABLE('a_manage');
    $curd->SET_PARAM('page_num', 20);
    if ($_REQUEST['curl_action'] == 'c') {
        $_REQUEST['regiest_time'] = time();
    }
    echo $curd->TAKE_ACTION($_REQUEST['curl_action'], $_REQUEST);
}
 * CURD 数据库 页面 结构
$widget = array(
    'id'       => array(
        'type'       => 'pk',   //状态类型 pk数据库主键 text:HTML文本类型 password:密码类型程序加密算法 email：邮箱类型，严重格式 time：时间戳 md5：程序加密 radio：单选框 select：下拉框 textarea：文本域
        'display'    => 'hide', //页面显示属性   hide隐藏  show显示  none不出现
        'allow_null' => true,   //可以为空选项  true可以为空   false不可以为空
        'style'      => '',     //HTML样式控制
        'title'      => 'ID',   //HTML标签标题控制
        'value'      => '',     //默认值
        'list'       => array(),//单选，复选，下拉框  选项
    ),
    'text'     => array(
        'type'       => 'text',
        'display'    => 'show',
        'allow_null' => false,
        'style'      => 'width:450px',
        'title'      => '用户名',
        'value'      => '',
    ),
    'password' => array(
        'type'       => 'password',
        'display'    => 'show',
        'allow_null' => false,
        'style'      => 'width:450px',
        'title'      => '密码',
        'value'      => '',
    ),
    'email'    => array(
        'type'       => 'email',
        'display'    => 'show',
        'allow_null' => false,
        'style'      => 'width:450px',
        'title'      => '邮箱',
        'value'      => '',
    ),
    'time'     => array(
        'type'       => 'time',
        'display'    => 'none',
        'allow_null' => true,
        'style'      => '',
        'title'      => '登录时间',
        'value'      => '',
    ),
    'md5'      => array(
        'type'       => 'md5',
        'display'    => 'none',
        'allow_null' => true,
        'style'      => '',
        'title'      => '单点登录TOKEN',
        'value'      => '',
    ),
    'radio'    => array(
        'type'       => 'radio',
        'display'    => 'show',
        'allow_null' => false,
        'style'      => 'margin-right: 30px',
        'title'      => '账号状态',
        'value'      => '',
        'list'       => array(
            0 => '关闭状态',
            1 => '启用状态',
        ),
    ),
    'select'   => array(
        'type'       => 'select',
        'display'    => 'show',
        'allow_null' => false,
        'style'      => 'width:450px',
        'title'      => '角色',
        'value'      => '',
        'list'       => array(
            '' => '请选择权限',
            1  => '内容编辑',
            2  => '审核，预编辑',
            4  => '发布管理',
            7  => '超级管理',
        ),
    ),
    'textarea' => array(
        'type'       => 'textarea',
        'display'    => 'show',
        'allow_null' => false,
        'style'      => 'width:450px;margin-left: 108px',
        'title'      => '姓名',
        'value'      => '',
    ),
);
* 
*/
class _CURD {

    public $CURD;

    public function __construct($conf) {
        //判断日志功能是否支持SQL记录，选择性开启日志功能
        $baseConf     = getConf();
        $_DB_Conf     = $baseConf['DB'];
        $_SMARTY_Conf = $baseConf['SMARTY'];

        $_SMARTY_Conf['TEMPLATE_DIR'] = dirname(__FILE__) . '/templates';
        $_DB                          = P('DB', $_DB_Conf);
        $_SMARTY                      = P('SMARTY', $_SMARTY_Conf);

        if (empty($_DB)) {
            throw new FWException('CURD DB is down', '500.1');
        }
        if (empty($_SMARTY)) {
            throw new FWException('CURD SMARTY is down', '500.1');
        }
        switch ($_DB_Conf['DB']) {
            case 'mysql':
                require_cache((dirname(__FILE__) . '/MysqlCURD.php'));
                $_CURD = MysqlCURD::getInstance($_DB, $_SMARTY, 'curd');
                break;
            default:
                require_cache((dirname(__FILE__) . '/MysqlCURD.php'));
                $_CURD = MysqlCURD::getInstance($_DB, $_SMARTY, 'curd');
                break;
        }
        $this->CURD = $_CURD;
    }

}


/*********************************************************************/
/*                             实际示例代码                           */
/********************************************************************/


////////////////////////////////////////////////////////////////////
//                             使用方法                            //
/*********************************************************************
public function manage() {
    $curd_a_manage = new Curd_a_manageObject();  //示例见下方数组
    $widget        = $curd_a_manage->getInfo();  //示例见下方数组
    $curd          = $this->GET_PACKAGE('CURD');
    $curd->SET_URL("http://" . $_SERVER ['HTTP_HOST'] . '/manage');
    $curd->SET_WIDGET($widget);
    $curd->SET_TABLE('a_manage');
    $curd->SET_PARAM('page_num', 20);
    if ($_REQUEST['curl_action'] == 'c') {
        $_REQUEST['regiest_time'] = time();
    }
    echo $curd->TAKE_ACTION($_REQUEST['curl_action'], $_REQUEST);
}
*********************************************************************/


////////////////////////////////////////////////////////////////////
//                             配置CURD对象                        //
/*********************************************************************
class Curd_a_manageObject {

    public $id              = array(
        'type'       => 'pk', //PRIMARY KEY
        'display'    => 'hide',
        'allow_null' => true,
        'style'      => '',
        'title'      => 'ID',
        'value'      => '');
    public $username        = array(
        'type'       => 'text',
        'display'    => 'show',
        'allow_null' => false,
        'style'      => 'width:450px',
        'title'      => '用户名',
        'value'      => '');
    public $password        = array(
        'type'       => 'password',
        'display'    => 'show',
        'allow_null' => false,
        'style'      => 'width:450px',
        'title'      => '密码',
        'value'      => '');
    public $email           = array(
        'type'       => 'email',
        'display'    => 'show',
        'allow_null' => false,
        'style'      => 'width:450px',
        'title'      => '邮箱',
        'value'      => '');
    public $realname        = array(
        'type'       => 'text',
        'display'    => 'show',
        'allow_null' => false,
        'style'      => 'width:450px',
        'title'      => '姓名',
        'value'      => '');
    public $last_login_time = array(
        'type'       => 'time',
        'display'    => 'none',
        'allow_null' => true,
        'style'      => '',
        'title'      => '登录时间',
        'value'      => '');
    public $regiest_time    = array(
        'type'       => 'time',
        'display'    => 'none',
        'allow_null' => true,
        'style'      => '',
        'title'      => '注册时间',
        'value'      => '');
    public $sso_token       = array(
        'type'       => 'md5',
        'display'    => 'none',
        'allow_null' => true,
        'style'      => '',
        'title'      => '单点登录TOKEN',
        'value'      => '');
    public $state           = array(
        'type'       => 'radio',
        'display'    => 'show',
        'allow_null' => false,
        'style'      => 'margin-right: 30px',
        'title'      => '账号状态',
        'value'      => '',
        'list'      => array(
            '0' => '关闭状态',
            '1' => '启用状态'
        )
    );
    public $role            = array(
        'type'       => 'select',
        'display'    => 'show',
        'allow_null' => false,
        'style'      => 'width:450px',
        'title'      => '角色',
        'value'      => '',
        'list'      => array(
            ''  => '请选择权限',
            '1' => '内容编辑',
            '2' => '审核，预编辑',
            '4' => '发布管理',
            '7' => '超级管理'
        )
    );
    public $demo            = array(
        'type'       => 'textarea',
        'display'    => 'show',
        'allow_null' => false,
        'style'      => 'width:450px;margin-left: 108px',
        'title'      => '姓名',
        'value'      => '');


    public function set($key, $value) {
        $this->$key = $value;
        return ture;
    }


    public function get($key) {
        return $this->$key;
    }

    public function getInfo() {
        return (array) $this;
    }

}
*/