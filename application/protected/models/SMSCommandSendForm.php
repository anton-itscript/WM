<?php

class SMSCommandSendForm extends CFormModel
{
    const SCENARIO_GENERATE = 'generate';
    const SCENARIO_SEND     = 'send';
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
            ['station_id, sms_command_code', 'required'],
            ['sms_command_message', 'required', 'on' => [self::SCENARIO_SEND]],
            ['station_id', 'in', 'range' => array_keys($this->getStations())],
            ['sms_command_code', 'in', 'range' => array_keys($this->getCommands())],
            ['sms_command_message', 'length', 'min' => 10, 'max' => 255, 'on' => [self::SCENARIO_SEND]]
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
        if (!$this->sms) {
            $this->sms = new SMSCommand();
        }
        return $this->sms;
    }

    /**
     * @param SMSCommand $sms
     *
     * @return SMSCommandSendForm $this
     */
    public function setSMS(SMSCommand $sms)
    {
        $this->sms = $sms;
        return $this;
    }

    public function attributeLabels()
    {
        return array(
            'station_id'=>'Station ID',
            'sms_command_code'=>'SMS Command code',
            'sms_command_message'=>'SMS Command message',

        );
    }
}