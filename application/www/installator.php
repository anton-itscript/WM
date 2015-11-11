<?php
date_default_timezone_set('UTC');
//	// Settings for production
//	error_reporting(E_NONE);
//	ini_set('display_errors', 0);
	
//	defined('YII_DEBUG') or define('YII_DEBUG', false);

	// Settings for development
//	It::clearApcCache();

	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	defined('YII_DEBUG') or define('YII_DEBUG', true);
	defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);
	
	// Some security issues
	header('X-Frame-Options: deny');
	
	//date_default_timezone_set('UTC');

	$protectedPath = dirname(__FILE__) . 
						DIRECTORY_SEPARATOR . '..' .
						DIRECTORY_SEPARATOR . 'protected';
	
	$yii = dirname(__FILE__) . 
			DIRECTORY_SEPARATOR . '..' .
			DIRECTORY_SEPARATOR . 'yii' . 
			DIRECTORY_SEPARATOR . 'yii.php';
	
	//$yii_lite = dirname(__FILE__) . 
	//				DIRECTORY_SEPARATOR . '..' .
	//				DIRECTORY_SEPARATOR . 'yii' . 
	//				DIRECTORY_SEPARATOR . 'yiilite.php';
	$config = $protectedPath . 
				DIRECTORY_SEPARATOR . 'config' . 
				DIRECTORY_SEPARATOR . 'install.php';

//	require_once($yii);

//	Yii::createWebApplication($config)->run();
?>