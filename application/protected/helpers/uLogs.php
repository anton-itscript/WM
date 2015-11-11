<?php
/**
 * Class uLogs
 * For log
 * ! Change $path
 */
class uLogs {
/*
 * Setting
 */
    
    protected static $file = "main_log";
    protected static $path = "/var/www/vhosts/demo_itscript_com/wm/log/";

    protected static $space = " ";
    protected static $tab = "\t";
    protected static $transfer = "\n";
    protected static $transfer_array = "";
    protected static $def_symbol = "\t";

    protected static $micro_time_max = 1;
    protected static $micro_time_round = 3;
    protected static $micro_time_format = "%-3s";
    protected static $time_format = "H:i:s ";
    protected static $date_format = "d/m ";
/*
 * Protected
 */
    protected static function file(){
        return static::$path.static::$file.".log";
    }
    protected static function symbol($symbol,$count=1){
        return $count ? $symbol.self::symbol($symbol,$count-1) : "";
    }
    protected static function tab($count=1){
        return self::symbol(static::$tab,$count);
    }
    protected static function space($count=1){
        return self::symbol(static::$space,$count);
    }
    protected static function transfer($count=1){
        return self::symbol(static::$transfer,$count);
    }
    protected static function defSymbol($count=1){
        return self::symbol(static::$def_symbol,$count);
    }
    protected static function string(){
        $msg='';
        $arg = func_get_arg(0);
        switch(gettype($arg)){
            case "array":
            case "object": {
                foreach($arg as $val)
                    $msg.=  self::string($val);
                return $msg.static::$transfer_array;}
            case "boolean": {
                $msg=$arg?"TRUE":"FALSE";
                break;}
            default: $msg=$arg;
        }
        return $msg.self::defSymbol();
    }
    public static function string_array($arg,$tab = 0){
        $msg='';
        switch(gettype($arg)){
            case "array":
            case "object": {
                $tab++;
                $msg.=gettype($arg).' = {'.static::$transfer;
                foreach($arg as $key => $val)
                    $msg.= self::tab($tab).'['.$key.'] => '.self::string_array($val,$tab);
                return $msg.self::tab($tab-1).'}'.static::$transfer;}
            case "boolean": {
                $msg=$arg?"TRUE":"FALSE";
                break;}
            default: $msg=$arg;
        }
        return $msg.self::$transfer;
    }
    /*
     * date function
     */
    public static function time_micro($time=false){
        $TIME = $time?$time:microtime(true);
        $return = number_format(
                        $time?$time:fmod($TIME,static::$micro_time_max),
                        static::$micro_time_round,
                        ".","");
        return sprintf(
            static::$micro_time_format,
            substr($return, 0,1)=='0'?
                substr($return, 2):
                $return.'s'
        );

    }
    public static function time($time=false){
        $TIME = $time? date(static::$time_format,$time) : date(static::$time_format);
        return $TIME;
    }
    public static function date($time=false){
        $TIME = $time? date(static::$date_format,$time) : date(static::$date_format);
        return $TIME;
    }
    /*
     * format function
     */
    public static function sting_min_len($len,$str){
        return sprintf("%-".$len."s",$str);
    }
    /*
     * Public
     */
    public static function msg(){
        error_log(static::string(func_get_args()), 3, self::file());
    }
    public static function msg_time(){
        static::msg(
            self::transfer().
            self::time().
            self::time_micro(),
            func_get_args()
        );
    }
    public static function msg_date(){
        static::msg(
            self::transfer().
            self::date().
            self::time(),
            func_get_args()
        );
    }

    public static function msg_array(){
        error_log(self::$transfer.self::string_array(func_get_arg(0)), 3, self::file());
    }

}
