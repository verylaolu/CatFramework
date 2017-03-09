<?php
/**
 * FW 框架启动文件 使用请调用RUN方法
 * @category   FW
 * @package  framework
 * @author    陆春宇
 */

begin();
$app_path=''; //动态APP项目加载名称（不可删除，使用方式见 function A()）

/**
 * 框架初始类
 */
class APP{
    /**
     * 启动方法
     */
    public static function RUN(){
        $CatFramework = new FW();
        $CatFramework->init();
    }
}
/**
 * 引入基本结构
 */
function begin(){
//    require_cache( FW_PATH.'/common/function.php');
    require_cache( FW_PATH.'/config/conf.common.php');
    require_cache( FW_PATH.'/config/conf.core.php');
    require_cache( FW_PATH.'/core/FW/FWRoute.php');
    require_cache( FW_PATH.'/core/FW/FW.php');
    require_cache( FW_PATH.'/core/FW/FWLibBase.php');
    require_cache( FW_PATH.'/core/FW/FWException.php');
    Route::checkRoute();
    if(!empty($_POST['uid'])){
        accessLog($_POST['uid']);
    }else{
        accessLog();
    }
    
}
//记录日志
function accessLog($uid='') {
    $conf = getConf();
    if(!$conf['LOG']['ACCESS_STATE']){
        return ;
    }
    $logModel = P('LOG', $conf['LOG']);
    $logModel->access($uid);
}


