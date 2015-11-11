<?php

class Import extends CFormModel {

    var $source_type;
    var $import_data;
    var $xml_file;
    
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }


    public function rules() {
        return array(

            array('import_data', 'required', 'on' => 'msg'),
            array('import_data', 'length', 'allowEmpty' => false, 'on' => 'msg'),
            array('source_type', 'numerical', 'integerOnly' => true, 'allowEmpty' => true, 'on' => 'msg'),
            array('xml_file', 'file', 'types' => 'xml', 'allowEmpty' => false, 'on' => 'xml')
        );
    }

    public function prepareTypes()
    {
        $types = array();
        $types[0] = 'Messages from AWS or RG ("$....@")';

        $station_types = array();
        $station_timezones = array();

        $sql = "SELECT * FROM `".Station::model()->tableName()."` WHERE `station_type` = 'rain' ORDER BY `station_id_code` ";
        $res = Yii::app()->db->createCommand($sql)->queryAll();



        if ($res) {
            foreach ($res as $key => $value) {
                $types[$value['station_id']] = "LOG from RG: ".$value['station_id_code']." (".$value['display_name'].")";
                $station_types[$value['station_id']] = $value['station_type'];

                $station_timezones[$value['station_id']] = $value['timezone_id'];
            }
        }

        return array('source_types' => $types, 'station_types' => $station_types, 'station_timezones' => $station_timezones);
    }

}