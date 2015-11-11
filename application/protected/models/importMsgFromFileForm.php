<?php

class importMsgFromFileForm extends CFormModel {

    /**
     * @var CUploadedFile
     */
    public $file;
    public $total_lines = 0;
    protected $valid_file_content = array();
    protected $errors = array();

    public static function model($className=__CLASS__) {
        return parent::model($className);
    }


    public function rules() {

        return array(
            array('file', 'file'),
        );
    }

    public function beforeValidate()
    {
        parent::beforeValidate();
        if($this->dataValidator()) {
            return true;
        }
    }

    public function dataValidator(){


        if(!is_object($this->file) )
            return false;

        $handle = @fopen($this->file->getTempName(), "r");

        $i=0;
        if ($handle) {
            while (($line = fgets($handle, 4096)) !== false) {
                $i++;
                $line = trim($line);
                if (!empty($line)) {

                    if(!preg_match("/^([@]{1}.+[\$]{1})$/i", $line, $matches)){
                        $this->addError('file',"incorrect message, line ".$i.": ".$line);
                    } else {
                        $this->valid_file_content[] = $line;
                    }

                }

            }

            if (!feof($handle)) {
                $this->errors[] =  "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        $this->total_lines = $i;
        if(count($this->errors)) {
            return false;
        }

        return true;
    }

    public function getValidFileContent()
    {
        return $this->valid_file_content;
    }

    public function createFileContent()
    {
        $fileString="";
        foreach ($this->valid_file_content as $line) {

            $fileString .= $line;
            $fileString .= "\r\n";
        }
        return $fileString;
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