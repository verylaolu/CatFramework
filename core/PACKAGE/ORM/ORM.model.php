<?php
class _ORM {

    public $ORM;

    public function __construct($conf) {
        $baseConf     = getConf();
        $_DB_Conf     = $baseConf['DB'];
        $_DB                          = P('DB', $_DB_Conf);
        if (empty($_DB)) {
            throw new FWException('CURD DB is down', '500.1');
        }
        switch ($_DB_Conf['DB']) {
            case 'mysql':
                require_cache((dirname(__FILE__) . '/MysqlORM.php'));
                $_ORM = MysqlORM::getInstance($_DB,$_DB_Conf, 'curd');
                break;
            default:
                require_cache((dirname(__FILE__) . '/MysqlORM.php'));
                $_ORM = MysqlORM::getInstance($_DB,$_DB_Conf, 'curd');
                break;
        }
        $this->ORM = $_ORM;
    }

}
