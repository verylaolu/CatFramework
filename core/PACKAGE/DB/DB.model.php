<?php
/**
 * FW DB包
 * @category   FW
 * @package  framework
 * @subpackage  core/PACKAGE/DB
 * @author    陆春宇
 */
class _DB {

    public $DB;

    public function __construct($conf) {
        //判断日志功能是否支持SQL记录，选择性开启日志功能
        $baseConf = getConf();
        if($baseConf['LOG']['SQL_STATE']){
            $LOG = P('LOG',  $baseConf['LOG']);
        }
        $db='';
        switch ($conf['DB']) {
            case 'Mysql':
                require_cache((dirname(__FILE__) . '/Mysql.model.php'));
                $db = Mysql::getInstance($conf, $conf['NAME'],$LOG);
                break;

            default:
                require_cache((dirname(__FILE__) . '/Mysql.model.php'));
                $db = Mysql::getInstance($conf, $conf['NAME']);
                break;
        }
        $this->DB = $db;
    }

}
