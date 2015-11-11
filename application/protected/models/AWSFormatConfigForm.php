<?php

class AWSFormatConfigForm extends CFormModel
{
    public $config;

    public function init()
    {
        foreach($this->getConfig() as $config) {
            $this->config[$config->key] = $config->value;
        }

        parent::init();
    }

    public function rules()
    {
        return [
            ['config','required']
        ];
    }

    public function getConfig()
    {
        return Config::get('AWS_');
    }

    public function attributeLabels()
    {
        $label = array();
        foreach($this->getConfig() as $config) {
            $label["config[{$config->key}]"] = $config->label;
        }

        return $label;
    }

    public function save()
    {
        foreach($this->config as $key => $val) {
            Config::set($key,$val);
        }
    }


    public static function listFormats()
    {
        return array('1'=>'old', '2'=>'new');
    }

    public function getAWSFormat()
    {
        return $this->config['AWS_FORMAT'];
    }

    public function isOldAWSFormat()
    {
        return $this->config['AWS_FORMAT'] ==  1 ? true : false ;
    }
}