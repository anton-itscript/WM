<?php

class SendSMSCommandForm extends CFormModel
{
    /** @var  int */
    public $station_id;
    /** @var  string */
    public $sms_command_code;
    /** @var  string */
    public $sms_command_message;

    /** @var  array */
    private $stations;
    /** @var  array */
    private $commands;

    /** @var  SMSCommand */
    private $sms;
    public function rules()
    {
        return [
            ['station_id, sms_command_message, sms_command_code', 'required'],
            ['station_id', 'in', 'range' => array_keys($this->getStations())],
            ['sms_command_code', 'in', 'range' => array_keys($this->getCommands())],
            ['sms_command_message', 'length', 'min' => 2, 'max' => 255]
        ];
    }
    /**
     * Return station list aws, rain
     *
     * @return array
     */
    public function getStations()
    {
        if (!$this->stations) {
            $this->stations = Station::prepareStationList(['aws', 'rain'],false);
        }

        return $this->stations;
    }

    /**
     * Return default template command
     *
     * @return array
     */
    public function getCommands()
    {
        if (!$this->commands) {
            $this->commands = SMSCommand::getSMSCommandsCode();
        }

        return $this->commands;
    }

    /**
     * @return SMSCommand|null
     */
    public function getSMS()
    {
        return $this->sms;
    }

    /**
     * @param SMSCommand $sms
     *
     * @return SendSMSCommandForm $this
     */
    public function setSMS(SMSCommand $sms)
    {
        $this->sms = $sms;
        return $this;
    }


}