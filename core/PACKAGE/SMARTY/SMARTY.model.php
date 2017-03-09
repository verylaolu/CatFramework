<?php
/**
 * FW SMARTY包
 * @category   FW
 * @package  framework
 * @subpackage  core/PACKAGE/SMARTY
 * @author    陆春宇
 */
class _SMARTY {

    public $SMARTY;
    public function __construct($conf) {
        require_cache((dirname(__FILE__) . '/libs/Smarty.class.php'));
        spl_autoload_register("__autoload");
        $smarty = new Smarty();
        $smarty->template_dir = $conf['TEMPLATE_DIR'];
        $smarty->compile_dir = $conf['COMPILE_DIR'];
        $smarty->config_dir = $conf['CONFIG_DIR'];
        $smarty->cache_dir = $conf['CACHE_DIR'];
        $smarty->left_delimiter = $conf['LEFT_DELIMITER']; //左定界符
        $smarty->right_delimiter = $conf['RIGHT_DELIMITER']; //右定界符
        $smarty->debugging = $conf['DEBUGGING'];
        $smarty->caching = $conf['CACHING'];
        $smarty->cache_lifetime = $conf['CACHE_LIFETIME'];
        $this->SMARTY = $smarty;
    }

}
