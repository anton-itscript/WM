<?php

class SMSCommandGenerateMessageForm extends CFormModel
{

    /** @var  array */
    public $sms_command_params = [];
    /** @var  int */
    public $station_id;
    /** @var  string */
    public $sms_command_code;

    /** @var  array */
    private $sms_command_params_list;

    public function rules()
    {
        return [
            ['station_id, sms_command_code', 'required'],
            ['sms_command_params', 'checkSMSCommandParams'],
        ];
    }

    public function getParamsList()
    {
        if (!$this->sms_command_params_list) {
            switch ($this->sms_command_code) {
            case 'DT':

                $this->sms_command_params_list['datetime'] = 'Datetime (YYMMDDHHMM)';
                break;

            case 'TI':

                $this->sms_command_params_list['mm'] = 'Transmission interval (mm)';
                break;

            case 'SM':

                $this->sms_command_params_list['phone'] = 'Telephone (integer only)';
                break;
            }
        }
        return $this->sms_command_params_list;
    }

    public function checkSMSCommandParams()
    {
        if ($this->getParamsList()) {
            foreach($this->getParamsList() as $key => $label) {
                if (!empty($this->sms_command_params[$key])) {
                    $callback = 'check' . ucfirst($key);
                    if (method_exists($this,$callback) && !$this->{$callback}($this->sms_command_params[$key])) {
                        $this->addError("sms_command_params[$key]",'Validate error.');
                    }
                } else {
                    $this->addError("sms_command_params[$key]",'Required.');
                }
            }
        }

        if ($this->hasErrors()) {
            return false;
        }
        return true;
    }

    public function checkDatetime($val)
    {
        if (strlen($val) == 10) {
            return true;
        } else {
            return false;
        }
    }

    public function checkMm($val)
    {
        if (strlen($val) == 2) {
            return true;
        } else {
            return false;
        }
    }

    public function checkPhone($val)
    {
        if (isset($val) && preg_match('/^\+?[0-9]+$/',$val)) {
            return true;
        } else {
            return false;
        }
    }

    public function generateMessage()
    {
        $station = Station::model()->findByPk($this->station_id);

        $command = 'C';
        $command .= $station->station_id_code;
        $command .= $this->sms_command_code;
        $command .= implode($this->sms_command_params ? $this->sms_command_params : []);
        $command .= It::prepareCRC($command);

        $command = '@'. $command .'$';

        return $command;
    }



}