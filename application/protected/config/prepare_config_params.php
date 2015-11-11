<?php
	function getConfigValue($configParam)
	{		
		static $values = null;

		if (is_null($values)){
			$configFile = dirname(__FILE__) . DIRECTORY_SEPARATOR .'install.conf';
			$values = parse_ini_file($configFile, true);
		}

		if ($values === false){
			return 'can\'t_parse_config';
		}
		switch ($configParam){
			case 'database_dsn':

				return 'mysql:host='. $values['database']['host'] .
						';port='. $values['database']['port'] .
						';dbname='. $values['database']['dbname'];

			case 'database_user':
			case 'database_password':
			case 'database_host':
			case 'database_port':
			case 'database_dbname':

				return $values['database'][str_replace('database_', '', $configParam)];

			case 'version_name':

				if (isset($values['version']['stage']) || isset($values['version']['sprint']) || isset($values['version']['update']))
				{
					return $values['version']['stage'] .'.'.
							$values['version']['sprint'] .'.'.
							$values['version']['update'];
				}
				else
				{
					return 'unknown';
				}


			case 'schedule':
			case 'version':
			case 'path':

				return $values[$configParam];
				
			case 'php_exe_path':
			case 'mysqldump_exe_path':
			case 'mysql_exe_path':
			case 'site_url_for_console':
				
				return $values['path'][$configParam];
				
			case 'defaultController':
				return 'site';

			default:
				return 'unknown_config_requested';
		}
	}
    function getConfigDbLongValue($configParam){
        static $values = null;

        if (is_null($values)){
            $configFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'longdb.conf';
            $values = parse_ini_file($configFile, true);
        }

        if ($values === false){
            return 'can\'t_parse_config';
        }
        switch ($configParam){
            case 'database_dsn':

                return 'mysql:host='. $values['database']['host'] .
                ';port='. $values['database']['port'] .
                ';dbname='. $values['database']['dbname'];

            case 'database_user':
            case 'database_password':
            case 'database_host':
            case 'database_port':
            case 'database_dbname':

                return $values['database'][str_replace('database_', '', $configParam)];

            default:
                return 'unknown_config_requested';
        }
    }

?>
