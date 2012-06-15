<?php
if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED',0);
}
error_reporting(E_ALL);
//error_reporting(0);
$base_uri=DIRECTORY_SEPARATOR=='/'?$_SERVER['SCRIPT_NAME']:str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URI',$base_uri=='/'?'':$base_uri);
define('APP_NAME', 'application');
define('APP_PATH', realpath(dirname(__FILE__)).'/');
define('SYS_PATH', APP_PATH.'../system/');
$G_LOAD_PATH=array(
    APP_PATH,
    SYS_PATH
);
$G_CONF_PATH=array(
    SYS_PATH.'configs/',
    APP_PATH.'configs/'
);
require_once (SYS_PATH.'functions.php');
ZHF::get_instance()->run();