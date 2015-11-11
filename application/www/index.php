<?php
/**
 * Header
 */

header('X-Frame-Options: deny');

date_default_timezone_set('UTC');
/**
 * debug mod
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
if (YII_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);
    defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_NONE);
}
error_reporting(E_ERROR);
$protectedPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'protected';

/**
 *	Check install
 */

$installConfig =  $protectedPath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'params' . DIRECTORY_SEPARATOR. 'install.php';
$installatorUrl = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://'.
                    $_SERVER['HTTP_HOST'] . preg_replace('/index\.php/', 'installator.php', $_SERVER['SCRIPT_NAME']);
$install =array();
if (is_file($installConfig)) {
    $install = require($installConfig);

}
if (!isset($install['install_status']) || $install['install_status'] != 1) {
    header("Location: ". $installatorUrl);
    exit;
}

/**
 * Run App
 */

$yii = $protectedPath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'yii' .  DIRECTORY_SEPARATOR . 'yii.php';
//$yii = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'yii' . DIRECTORY_SEPARATOR . 'yiilite.php';
$config = $protectedPath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'main.php';

require_once($yii);
Yii::createWebApplication($config)->run();