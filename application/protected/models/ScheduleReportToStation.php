<?php

class ScheduleReportToStation extends CStubActiveRecord
{


    public static function model($className=__CLASS__){
        return parent::model($className);
    }
    public function beforeSave(){

        return parent::beforeSave();
    }

	public function tableName()
	{
		return 'schedule_report_to_station';
	}

    public function rules()
	{
        return array(

            array('schedule_id', 'required'),
            array('station_id', 'safe'),

        );
    }
    
	public function relations()
    {
        return array(
            'realStation' => array(self::BELONGS_TO, 'Station', 'station_id','alias'=>'realStation'),
            'station' => array(self::BELONGS_TO, 'Station', 'station_id'),
            'schedule_report' => array(self::BELONGS_TO, 'ScheduleReport', 'schedule_id'),
            'processed' => array(self::HAS_MANY, 'ScheduleReportProcessed', 'sr_to_s_id',
                    'on'=>'is_processed=1 and is_last=1'
            ),
        );
    }

    public function beforeDelete()
    {
       return parent::beforeDelete();
    }


    public function attributeLabels()
	{
        return array (
            'station_id'        => Yii::t('project', 'Station ID'),
        );
    }

}