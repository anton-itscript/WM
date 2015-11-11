<?php 
 return array (
    'install'=>                 requireMorefiles(
    array(
        dirname(__FILE__) . DIRECTORY_SEPARATOR .'params'. DIRECTORY_SEPARATOR .'install.php',
        dirname(__FILE__) . DIRECTORY_SEPARATOR .'params'. DIRECTORY_SEPARATOR .'install_db.php',
        dirname(__FILE__) . DIRECTORY_SEPARATOR .'params'. DIRECTORY_SEPARATOR .'install_db_long.php',
    )),
    'applications'=> 	        requireIfFileExist(dirname(__FILE__) . DIRECTORY_SEPARATOR .'params'. DIRECTORY_SEPARATOR .'application_params.php'),
    'db_long_sync_config'=> 	requireIfFileExist(dirname(__FILE__) . DIRECTORY_SEPARATOR .'params'. DIRECTORY_SEPARATOR .'database_long_sync_config.php'),
    'schedule_params'=> 	    requireIfFileExist(dirname(__FILE__) . DIRECTORY_SEPARATOR .'params'. DIRECTORY_SEPARATOR .'schedule_params.php'),
     'db_params'        =>      requireIfFileExist(dirname(__FILE__) . DIRECTORY_SEPARATOR .'params'. DIRECTORY_SEPARATOR .'db_params.php'),
     'db_long_params'        =>      requireIfFileExist(dirname(__FILE__) . DIRECTORY_SEPARATOR .'params'. DIRECTORY_SEPARATOR .'db_long_params.php'),
     'console_app_path' => dirname(__FILE__) .
         DIRECTORY_SEPARATOR .'..'.
         DIRECTORY_SEPARATOR .'..'.
         DIRECTORY_SEPARATOR . 'www'.DIRECTORY_SEPARATOR.'console.php',

     'backups_path' => dirname(__FILE__) .
         DIRECTORY_SEPARATOR .'..'.
         DIRECTORY_SEPARATOR .'..'.
         DIRECTORY_SEPARATOR .'www'.
         DIRECTORY_SEPARATOR .'files'.
         DIRECTORY_SEPARATOR .'backups',
  'show_fake_com_ports' => false,
  'show_msg_generation' => true,
  'version_name' => 
  array (
    'stage' => '0',
    'sprint' => '07',
    'update' => '00',
  ),
  'com_connect_params' => 
  array (
    'baudrate' => 9600,
    'databits' => 8,
    'stopbits' => 1,
    'parity' => 'none',
    'hardwareflowcontrol' => 'rts/cts',
  ),
  'com_for_send_sms_command' => 'COM3',
  'station_type' => 
  array (
    'rain' => 'Rain',
    'aws' => 'AWS',
    'awos' => 'AWOS',
  ),
  'user_role' => 
  array (
    'superadmin' => 'superadmin',
    'admin' => 'admin',
    'user' => 'user',
  ),
  'enable' => 
  array (
    1 => 'Enable',
    0 => 'Disable',
  ),
  'controllers' => 
  array (
    0 => 'superadmin',
    1 => 'admin',
    2 => 'site',
  ),
  'com_type' => 
  array (
    'direct' => 'Direct Serial',
    'sms' => 'Direct SMS',
    'tcpip' => 'TCP/IP',
    'gprs' => 'GPRS',
    'server' => 'TCP/IP server',
  ),
  'bucket_sizes' => 
  array (
    '0.1' => 0.10000000000000001,
    '0.2' => 0.20000000000000001,
    '0.25' => 0.25,
    '0.5' => 0.5,
  ),
  'status_message_period' => 
  array (
    60 => '1 Hour',
    120 => '2 Hours',
    180 => '3 Hours',
  ),
  'event_message_period' => 
  array (
    5 => '5 Minutes',
    10 => '10 Minutes',
    15 => '15 Minutes',
    20 => '20 Minutes',
    30 => '30 Minutes',
    60 => '1 Hour',
  ),
  'schedule_generation_period' => 
  array (
    15 => '15 Min',
    30 => '30 Min',
    60 => '1 hour',
    120 => '2 hour',
    180 => '3 hour',
    360 => '6 hour',
    540 => '9 hour',
    720 => '12 hour',
    900 => '15 hour',
    1080 => '18 hour',
    1440 => '24 hour',
  ),
  'schedule_report_type' => 
  array (
    'synop' => 'SYNOP',
    'bufr' => 'BUFR',
    'metar' => 'METAR',
    'speci' => 'SPECI',
    'data_export' => 'Export Data',
  ),
  'schedule_delivery_method' => 
  array (
    'mail' => 'Mail',
    'ftp' => 'FTP',
    'local_folder' => 'Folder at Local PC',
  ),
  'schedule_report_format' => 
  array (
    'txt' => 'txt',
    'csv' => 'csv',
  ),
  'sendmail_fake_params' => 
  array (
    'enabled' => true,
    'SMTPDebug' => true,
    'Mailer' => 'sendmail',
      'Sendmail'  =>
          dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'www'
          . DIRECTORY_SEPARATOR . 'sendmail' . DIRECTORY_SEPARATOR . 'sendmail.exe -t',
  ),
  'xml_check_frequency' => 
  array (
    5 => '5 minutes',
    15 => '15 minutes',
    30 => '30 minutes',
    60 => '1 hour',
  ),
  'smtps_support_options' => 
  array (
    'auto' => 'auto (use SSL for port 465, otherwise try to use TLS)',
    'ssl' => 'ssl (always use SSL)',
    'tls' => 'tls (always use TLS)',
    'none' => 'none (never try to use SSL)',
  ),
  'sms_params' => 
  array (
    'serial_port' => 'COM2',
    'failure_count_before_send_sms' => 3,
    'failure_timeout' => 600,
    'station_code' => 'OXBOW',
    'enabled' => false,
  ),
  'polling_params' => 
  array (
    'interval' => 600,
    'failure_count_before_modem_reset' => 3,
    'enabled' => true,
  ),
  'client_code' => 'Lesotho',
  'station_gravity' => 
  array (
    '9.8066500000' => 'WMO',
    0 => 'Custom',
  ),
  'developer_email' => 'dmitriy.gorkovoy@itscript.com',
    'CONST'=>array(
        'delete_periodicity'=>array(
                                'MINUTE' => 'MINUTES',
                                'HOUR'   => 'HOURS',
                                'DAY'    => 'DAYS',
                                'WEEK'   => 'WEEKS',
                                'MONTH'  => 'MONTHS',
                                'QUARTER'=> 'QUARTERS',
                                'YEAR'   => 'YEARS',
                            ),
        'periodicity'=>array(
                            'minutely'  => 'Minutely',
                            'hourly'    => 'Hourly',
                            'daily'     => 'Daily',
                            'weekly'    => 'Weekly',
                            )
    ),
) 
 ?>