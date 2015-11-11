<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'applications_param_incl.php');

	return array(
		'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR .'..',
		'name' => 'Delairco: Weather Monitor',
		
		'defaultController' => 'site',
        'catchAllRequest' => array('maintenance/index'),

        'sourceLanguage' => '00',
		'language' => 'en-GB',
		
		'preload' => array('log'),

		// autoloading model and component classes
		'import' => array(
			'application.models.*',
            'application.filemodels.*',
			'application.components.*',
			'application.validators.*',
			'application.helpers.*',
            'application.helpers.Communication.*',
            'application.helpers.WeatherReport.*',
            'application.helpers.WeatherTypeReport.*',
            'application.helpers.ProcessListen.*',
            'application.helpers.ParseMessage.*',
            'application.helpers.ProcessForwarded.*',
            'application.helpers.Synchronization.*',
            'application.helpers.Patterns.*',
            'application.helpers.Senders.*',
            'application.helpers.Log.*',
            'application.helpers.SlaveMasterExchange.*',
            'application.helpers.SensorHandler.*',
            'application.helpers.CalculationHandler.*',
            'application.helpers.ScheduleReports.*',
            'application.extensions.ECSVExporter',
            'application.extensions.ECSVImporter',
            'application.widgets.MainMenu.MainMenu',
            'application.widgets.mailSender.mailSender',
			'application.extensions.Color',
			'application.extensions.TextFileWorker',
		),

		// application components
		'components' => array(
			'db'      => require(dirname(__FILE__) . DIRECTORY_SEPARATOR .'params'. DIRECTORY_SEPARATOR .'mysql.php'),
			'db_long' => require(dirname(__FILE__) . DIRECTORY_SEPARATOR .'params'. DIRECTORY_SEPARATOR .'mysql_long.php'),

//			'assetManager' => array(
//				'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'www'. DIRECTORY_SEPARATOR .'assets',
//				'baseUrl'  => $base_url . '/assets/',
//			),
			
			'urlManager' => array(
				'urlFormat' => 'path',
				'showScriptName' => false,
			),
            'errorHandler'=>array(
                // use 'site/error' action to display errors
                'errorAction'=>'site/error',
            ),
			'log' => array(
				'class' => 'CLogRouter',
				'routes' => array(
					//For production
					array(
						'class' => 'CFileLogRoute',
						'logFile' => 'application_'. date('l') .'.log',
						'levels' => 'warning, error',
						'enabled' => true,
					),
					// For development.
					array(
						'class' => 'CWebLogRoute',
                        'levels' => 'trace, info, profile, warning, error',
//                        'levels' => 'warning, error',
						'enabled' => false,
					),

                    'db' => array(
                        'class' => 'CFileLogRoute',
                        'logFile' => 'db_application_'. date('l') .'.log',
                    )
				),
			),

			'user' => array(
				'loginUrl' => array('site/login'),
				'class' => 'WebUser',
				'allowAutoLogin' => true,
			),

			'session' => array(
				'autoStart' => true,
			),

			'mailer' => array(
				'class'       => 'application.extensions.mailer.EMailer',
				'pathViews'   => 'application.views.email',
				'pathLayouts' => 'application.views.email.layouts'
			),

            'clientScript' => require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'clientScript.php'),
		),

        'params' => require(dirname(__FILE__) . DIRECTORY_SEPARATOR .'params.php'),
	);
?>