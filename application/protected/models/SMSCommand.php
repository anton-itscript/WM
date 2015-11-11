<?php

/**
 * Class SMSCommand
 *
 * @property $sms_command_id
 * @property $sms_command_status
 * @property $station_id
 * @property $sms_command_code
 * @property $sms_command_message
 * @property $sms_command_response
 * @property $updated
 * @property $created
 *
 * @property Station $station
 */
class SMSCommand extends CStubActiveRecord
{
    const STATUS_NEW       = 'new';
    const STATUS_SENT      = 'sent';
    const STATUS_PROCESSED = 'processed';

    public $from_date;
    public $to_date;
    public  function tableName()
    {
        return 'sms_command';
    }

    /**
     * @param string $className
     *
     * @return SMSCommand
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        return [
            ['sms_command_status, station_id, sms_command_code, sms_command_message', 'required'],
            ['from_date, to_date', 'safe'],
            ['sms_command_status', 'in', 'range' => [self::STATUS_NEW, self::STATUS_PROCESSED, self::STATUS_SENT]],
            ['sms_command_code', 'in', 'range' => array_keys(self::getSMSCommandsCode())],
        ];
    }

    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            $this->sms_command_status = self::STATUS_NEW;
        }

        return parent::beforeValidate();
    }

    public function beforeSave()
    {
        if ($this->isNewRecord) {
            $this->created = new CDbExpression('NOW()');
        }
        $this->updated = new CDbExpression('NOW()');

        return parent::beforeSave();
    }

    public function relations()
    {
        return array(
            'station' => array(self::BELONGS_TO, 'Station', 'station_id'),
        );
    }

    public function attributeLabels()
    {
        return [
            'sms_command_status'   => 'Status',
            'station_id'           => 'Station Id',
            'sms_command_code'     => 'Code',
            'sms_command_message'  => 'Message',
            'sms_command_response' => 'Response',
            'updated'              => 'Updated',
            'created'              => 'Created',
        ];
    }

    /**
     * Return available commands
     *
     * @return array - [sms_command_code => title]
     */
    public static function getSMSCommandsCode()
    {
        return [
            'SR' => 'Status Request',
            'DT' => 'Change Date & Time',
            'RT' => 'Remote Reset/Restart Command',
            'TI' => 'Change Data Transmission Interval',
            'DR' => 'Data Packet Request',
            'SM' => 'Change SMS Number Request',
        ];
    }

    /**
     * Find current sms command and set in here response
     *
     * @param $response string
     * @example $response OK   : '@ B AWS01 DT OK   234567890 BV1 126 7558E9F6 $' (without space)
     * @example $response FAIL : '@ B AWS01 DT FAIL                   E3290CA6 $' (without space)
     *
     * @return null|SMSCommand
     */
    public static function setResponse($response)
    {
        /**
         * 1. Min response length
         * 2. Lead X code
         * 3. Start @
         *    Ended $
         * 4. CRC
         */
        if (strlen($response) >= 18
            && (substr($response, 1, 1) === 'B' || substr($response, 1, 1) === 'C')
            && substr($response, 0, 1) === '@'
            && substr($response, -1) === '$'
            && substr($response, -9, 8) === It::prepareCRC(substr($response, 1, -9))
        ) {
            $sms_command_code  = substr($response, 7, 2);
            $station_id_code   = substr($response, 2, 5);

            $qb = new CDbCriteria();
            $qb->compare('sms_command_status', SMSCommand::STATUS_SENT);
            $qb->compare('sms_command_code', $sms_command_code);
            $qb->compare('station.station_id_code', $station_id_code);
            $qb->with = [ 'station' => ['select' => false]];
            $qb->order = 't.updated ASC';
            /** @var SMSCommand $sms_command */
            $sms_command = SMSCommand::model()->find($qb);

            if (!isset($sms_command)) {
                $sms_command = new SMSCommand();
                $station = Station::Model()->findByAttributes(array('station_id_code'=>$station_id_code));

                if(is_null($station))
                    return null;

                $sms_command->station_id = $station->station_id;
                $sms_command->sms_command_code = "-";
                $sms_command->sms_command_message = "-";
            }

            $sms_command->sms_command_response = $response;
            $sms_command->sms_command_status = SMSCommand::STATUS_PROCESSED;
            if (!$sms_command->save()) {
                It::sendLetter(yii::app()->params['developer_email'],'Error', json_encode($sms_command->getErrors()));
            }
            return $sms_command;

        }

        return null;
    }

    public function search()
    {
        $criteria=new CDbCriteria;


        if (!empty($this->from_date) && empty($this->to_date)) {
            $criteria->condition = "updated >= '$this->from_date'";  // date is database date column field
        }

        if(!empty($this->to_date) && empty($this->from_date)) {
            $criteria->condition = "updated <= '$this->to_date'";
        }

        if(!empty($this->to_date) && !empty($this->from_date)) {
            $criteria->condition = "updated  >= '$this->from_date' and updated <= '$this->to_date'";
        }

        $criteria->compare('sms_command_id',$this->sms_command_id,true);
        $criteria->compare('sms_command_status',$this->sms_command_status,true);
        $criteria->compare('station_id',$this->station_id,true);
        $criteria->compare('sms_command_code',$this->sms_command_code,true);
        $criteria->compare('sms_command_message',$this->sms_command_message,true);
        $criteria->compare('sms_command_response',$this->sms_command_response,true);
        $criteria->compare('updated',$this->updated,true);
        $criteria->compare('created',$this->created,true);

        return new CActiveDataProvider(
            $this, array(
                'criteria' => $criteria,
                'sort'     => array(
                    'defaultOrder' => array('updated' => true),

                ),

            )
        );
    }
}