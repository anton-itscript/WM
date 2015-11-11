<?php

/**
 * Class sqlBuilder
 * For create BIG insert data
 */
class sqlBuilder {

    protected static function returnOrderObject($obj){
        $return_arr = array();
        foreach($obj as $key => $val)
            $return_arr[$key]=$val;
        ksort($return_arr);
        return $return_arr;
    }
    protected static function createInsertValueFromObject($obj){
        $sql=' (';
        $arr = self::returnOrderObject($obj);
        foreach($arr as $key => $val){
            if(!is_null($val)){
                switch ($key){
                    case 'measuring_timestamp':{
                        $sql.='DATE_FORMAT(\''.$val.'\',GET_FORMAT(DATETIME,\'ISO\')),'; break;}
                    case 'created':
                    case 'updated':{
                        if($val=="NOW()") $sql.=$val.',';
                        else $sql.='DATE_FORMAT(\''.$val.'\',GET_FORMAT(DATETIME,\'ISO\')),';
                        break;}
                    case 'sensor_data_id':{
                        $sql.=$val.','; break;}
                    default:{
                        $sql.='\''.$val.'\',';}
                }
            }
        }
        $sql=substr($sql, 0, strlen($sql)-1);

        $sql.=')';
        return $sql;
    }
    protected static function createInsertTitleFromObject($obj,$tableName){
        $sql = 'REPLACE INTO `'.$tableName.'` (';
        $arr = self::returnOrderObject($obj);
        foreach($arr as $key => $val){
            if(!is_null($val))
                $sql.='`'.$key.'`,';
        }
        $sql=substr($sql, 0, strlen($sql)-1);
        $sql.=') VALUES';

        return $sql;
    }
    public static function createInsertFromObject($obj,$tableName,$onlyData = false){
        $sql = '';
        if(!$onlyData) $sql.= self::createInsertTitleFromObject($obj,$tableName);
        else $sql.=",";

        $sql.="\n";
        $sql.=self::createInsertValueFromObject($obj);
        return $sql;
    }








    public static function createInsertFromArray($array, $tableName)
    {
        if(count($array)==0)
            return false;
        $sql = '';
        $sql.= self::createReplaceTitleFromArray($array,$tableName);
        $sql.= "\n";
        for($i=0;$i<count($array);$i++) {
            $sql .= self::createFieldsValues($array[$i]);
        }
        $sql=substr($sql, 0, strlen($sql)-2);
        $sql.= ";\n";
        return $sql;
    }

    protected static function createReplaceTitleFromArray($array,$tableName)
    {
        $fieldsNames = self::getFieldsNames($array);
        $sql ="\n";
        $sql.= 'REPLACE INTO `'.$tableName.'` (';
        $sql.= '`'.implode('`,`',$fieldsNames).'`';
        $sql.=') VALUES';
        return $sql;
    }

    protected static function getFieldsNames($array)
    {
        $header_array=array();
        for($i=0;$i<count($array);$i++){
            $header_array = array_merge($header_array,$array[$i]);

        }
        return array_keys($header_array);

    }

    protected static function createFieldsValues($array)
    {

        $sql=' (';
        foreach($array as $key => $val){
            if(!is_null($val)){
                switch ($key){
                    case 'measuring_timestamp':{
                        $sql.='DATE_FORMAT(\''.$val.'\',GET_FORMAT(DATETIME,\'ISO\')),'; break;}
                    case 'created':
                    case 'updated':{
                        if($val=="NOW()") $sql.=$val.',';
                        else $sql.='DATE_FORMAT(\''.$val.'\',GET_FORMAT(DATETIME,\'ISO\')),';
                        break;}
                    case 'sensor_data_id':{
                        $sql.=$val.','; break;}
                    default:{
                    $sql.='\''.$val.'\',';}
                }
            }
            else {
                $sql.='null,';
            }
        }
        $sql=substr($sql, 0, strlen($sql)-1);

        $sql.='),';
        $sql.="\n";
        return $sql;
    }

    public static function createTruncateTableCommand($tableName)
    {
        return "\nTRUNCATE TABLE `".$tableName."`;";
    }

}