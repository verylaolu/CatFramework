<?php

/**
 * FW 框架启动文件 使用请调用RUN方法
 * @category   FW
 * @package  framework
 * @author    陆春宇
 */
//begin();
$app_path = ''; //动态APP项目加载名称（不可删除，使用方式见 function A()）

/**
 * 框架初始类
 */
class APP {

    public function __construct() {
        require( FW_PATH . '/common/function.php');
        require_cache(FW_PATH . '/config/conf.common.php');
        require_cache(FW_PATH . '/config/conf.core.php');
        require_cache(FW_PATH . '/core/FW/FWRoute.php');
        require_cache(FW_PATH . '/core/FW/FW.php');
        require_cache(FW_PATH . '/core/FW/FWLibBase.php');
        require_cache(FW_PATH . '/core/FW/FWException.php');
        Route::checkRoute();
        if (!empty($_POST['uid'])) {
            $this->accessLog($_POST['uid']);
        } else {
            $this->accessLog();
        }
    }

    public function accessLog($uid = '') {
        $conf = getConf();
        if (!$conf['LOG']['ACCESS_STATE']) {
            return;
        }
        $logModel = P('LOG', $conf['LOG']);
        $logModel->access($uid);
    }

    /**
     * 启动方法
     */
    public static function RUN() {
        $app          = new APP();
        $CatFramework = new FW();
        $CatFramework->init();
    }

    public static function MODULE($appname) {
        if (!file_exists(DIR_PATH . '/app_' . $appname . '/index.php')) {
              $appname = BASE_APP;
        }
        require DIR_PATH . '/app_' . $appname . '/index.php';
    }

}
