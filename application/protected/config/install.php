<?php

    require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'applications_param_incl.php');

	// This is the main Web application configuration. Any writable
	// application properties can be configured here.
	return array(
		'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR .'..',
		'name' => 'Delairco: Install',

		'defaultController' => 'install',


		// autoloading model and component classes
		'import' => array(
			'application.models.*',
			'application.components.*',
			'application.helpers.*',
		),

		// application components
		'components' => array(

			'log' => array(
				'class' => 'CLogRouter',
				'routes' => array(
					array(
						'class' => 'CWebLogRoute',
						'levels' => 'trace, info, profile, warning, error',
						'enabled' => false,
					),
				),
			),
		),

        'params' => require(dirname(__FILE__) . DIRECTORY_SEPARATOR .'params.php'),

    );