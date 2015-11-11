<?php 
 return array (
  'class' => 'CDbConnection',
  'connectionString' => 'mysql:host=localhost;port=3306;dbname=wm_docker_long',
  'username' => 'wm',
  'password' => 'wm_pass',
  'charset' => 'utf8',
  'emulatePrepare' => true,
  'enableParamLogging' => false,
  'enableProfiling' => false,
  'persistent' => true,
  'initSQLs' => 
  array (
    0 => 'set time_zone=\'+00:00\';',
  ),
) 
 ?>