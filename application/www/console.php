<?php
date_default_timezone_set('UTC');
	set_time_limit(0);
	ini_set('memory_limit', '-1');

	error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);
	defined('YII_DEBUG') or define('YII_DEBUG', true);

	$protectedPath = dirname(__FILE__) . 
						DIRECTORY_SEPARATOR . '..' .
						DIRECTORY_SEPARATOR . 'protected';

	$yii = dirname(__FILE__) . 
			DIRECTORY_SEPARATOR . '..' .
			DIRECTORY_SEPARATOR . 'yii' . 
			DIRECTORY_SEPARATOR . 'yii.php';

	$config = $protectedPath . 
				DIRECTORY_SEPARATOR . 'config' . 
				DIRECTORY_SEPARATOR . 'console.php';

	require_once($yii);

	try {
		Yii::createConsoleApplication($config)->run();		
	} catch (Exception $e) {
		print_r($e->getMessage());
	}