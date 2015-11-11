<?php

class SuperAdminConfigForm extends CFormModel
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
        return Config::get('SITE_');
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
}