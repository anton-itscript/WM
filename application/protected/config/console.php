<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'applications_param_incl.php');

	return array(
		'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
		'name' => 'Ports Listening',
		
		'params' => require(dirname(__FILE__) . DIRECTORY_SEPARATOR .'params.php' ),

		'import' => array(
			'application.models.*',
			'application.filemodels.*',
			'application.components.*',
			'application.helpers.*',
			'application.helpers.Communication.*',
			'application.helpers.WeatherReport.*',
            'application.helpers.WeatherTypeReport.*',
            'application.extensions.ECSVExporter',
            'application.extensions.ECSVImporter',
			'application.helpers.ProcessListen.*',
			'application.helpers.ParseMessage.*',
			'application.helpers.ProcessForwarded.*',
			'application.helpers.Synchronization.*',
            'application.helpers.ScheduleReports.*',
			'application.helpers.Patterns.*',
			'application.helpers.Log.*',
			'application.helpers.SlaveMasterExchange.*',
			'application.helpers.Senders.*',
			'application.widgets.*',
            'application.widgets.mailSender.mailSender',
            'application.filters.*',
            'application.views.*',
		),

		'preload' => array('log'),


		// application components
		'components' => array(
            'db' =>	require(dirname(__FILE__) .
                    DIRECTORY_SEPARATOR .'params'.
                    DIRECTORY_SEPARATOR .'mysql.php'),

            'db_long' =>	require(dirname(__FILE__) .
                    DIRECTORY_SEPARATOR .'params'.
                    DIRECTORY_SEPARATOR .'mysql_long.php'),

            'mutex' => array(
                'class' => 'application.extensions.EMutex',
            ),

			'log' => array(
				'class' => 'CLogRouter',
				'routes' => array(
					array(
						'class' => 'CFileLogRoute',
						'logFile' => 'console_'. date('l') .'.log',
						'levels' => 'error, warning',
						'enabled' => true,
					),

                    'db' => array(
                        'class'     => 'CFileLogRoute',
                        'logFile'   => 'db_console_'. date('l') .'.log',
                       // 'levels'=>'error, warning, trace, info',
                    )
				),
			),

			'user' => array(
				'allowAutoLogin' => true,
				'loginUrl' => array('user'),
				'class' => 'WebUser',
			),

			'mailer' => array(
				'class'       => 'application.extensions.mailer.EMailer',
				'pathViews'   => 'application.views.email',
				'pathLayouts' => 'application.views.email.layouts'
			), 
		)
	);
?>