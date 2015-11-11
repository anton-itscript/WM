<?php
/*
 * work with .conf file
 */
class ConfigManager{
    protected static $file = 'longdb';

    //get path
    public static function getConfigFile(){
        return Yii::app()->basePath .
        DIRECTORY_SEPARATOR .'config'.
        DIRECTORY_SEPARATOR .static::$file.'.conf';
    }
    //get config section
    public static function getConfigSection($sectionName,$atr = null){
        $values = static::getConfigValues();
        if (isset($values[$sectionName])){
            if($atr!=null) return $values[$sectionName][$atr];
            return $values[$sectionName];
        }
        return null;
    }
    //get full config
    public static function getConfigValues(){
        $configFile = static::getConfigFile();
        $values = parse_ini_file($configFile, true);
        if ($values === false)
            $values = array();

        return $values;
    }
    //set section
    public static function setConfigSection($sectionName, $sectionValues){
        $configFile = static::getConfigFile();

        $values = static::getConfigValues();
        $values[$sectionName] = $sectionValues;

        if (is_writable($configFile)) {

            $handle = @fopen($configFile, 'w+');
            if ($handle === false) return false;

            foreach ($values as $section => $sections){
                @fwrite($handle, '['. $section .']'."\n");
                if (is_array($sections)){
                    foreach ($sections as $key => $value)
                        @fwrite($handle, $key .' = '. $value . "\n");
                }
                @fwrite($handle, "\n");
            }

            @fclose($handle);
            return true;
        }

        return false;
    }
    //set section option
    public static function setConfigSectionOption($sectionName, $optionName, $optionValues=''){
        $array = static::getConfigSection($sectionName);
        $array[$optionName] = $optionValues;
        static::setConfigSection($sectionName,$array);
    }
    //construct
    public function __construct($filename='longdb') {
        static::$file = $filename;
    }

}