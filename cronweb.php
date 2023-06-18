<?php
/**
 * @category Prestashop
 * @category Module
 * @author Samdha <contact@samdha.net>
 * @copyright Samdha
 * @license commercial license see license.txt
 **/

define('_PS_ADMIN_DIR_', 1);


require(dirname(__FILE__) . '/../../config/config.inc.php');
//include_once(_PS_ROOT_DIR_.'/init.php');
if (file_exists(_PS_ROOT_DIR_ . '/images.inc.php')) {
    include_once(_PS_ROOT_DIR_ . '/images.inc.php');
}

@ini_set('display_errors', 'on');
@error_reporting(E_ALL | E_STRICT);


 function cronweb($argv)
{
    $action = $argv[0];

    if ($action != "") {
        echo "c parti <br>";
        $start = microtime(true);
        $module_name = basename(dirname(__FILE__));
        $module = Module::getInstanceByName($module_name);

        if ($module->active) {
            $module->cron($action, $argv);
        }
        $end = microtime(true);

        echo $end - $start;
    }

}

cronweb(["SendOrder"]);
cronweb(["UpdatePriceAndQuantity"]);