<?php

class ScheduleTypeReport extends CStubActiveRecord
{
	public $nextScheduleTime;
	public $nextScheduleUnixTime;
	public $start_date;
	public $start_time;
	public $parse_time = true;
	public $generation_delay_in_seconds = 0;

    public $start_datetime_delayed;
    public $next_run_planned_delayed;

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
    }

    public function tableName()
	{
		return 'ex_schedule_report';
    }

    public function rules()
	{
        return array(
            array('start_date', 'match', 'pattern' => '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/'),
            array('start_time', 'match', 'pattern' => '/^(\d{1,2})\/(\d{1,2})$/'),
            array('start_time', 'length', 'max' => 5),
            array('generation_delay', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('aging_time_delay', 'match', 'pattern' => '/^(\d{1,})$/'),
            array('ex_schedule_ident', 'unique'),
            array('report_type, ex_schedule_ident, report_format, station_type, period, aging_time_delay', 'required'),
            array('send_email_together, send_like_attach, sent, current_role', 'safe'),
        );
    }
	
	public function relations()
    {
        return array(
            'destinations' => array(self::HAS_MANY, 'ScheduleTypeReportDestination', 'ex_schedule_id'),
        );
    }

    public function beforeSave()
	{
        if (!$this->getUseLong()) {
            if ($this->isNewRecord) {
                $this->created = new CDbExpression('NOW()');
                $this->setTimes();
            }
            $this->updated = new CDbExpression('NOW()');
            if($this->parse_time && !empty($this->start_time)&& !empty($this->start_date)) {
                preg_match("/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/", $this->start_date, $start_date_matches);
                preg_match("/^(\d{1,2})\/(\d{1,2})$/", $this->start_time, $start_time_matches);

                $this->next_run_planned =   $start_date_matches[3].'-'.
                                            $start_date_matches[1].'-'.
                                            $start_date_matches[2].' '.
                                            $start_time_matches[1].':'.
                                            $start_time_matches[2].':'.
                                            '00';

                $this->start_datetime = date('Y-m-d H:i:s',((It::timeToUnixTimestamp($this->next_run_planned)) - ($this->period*60)));
            }


        }
        return parent::beforeSave();
    }

    public function afterFind()
    {
        if (!$this->getUseLong()) {

            preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/",$this->next_run_planned,$date_matches);

            $this->start_date  = $date_matches[2].'/'.$date_matches[3].'/'.$date_matches[1];
            $this->start_time  = $date_matches[4].'/'.$date_matches[5];
            $this->generationDelay();
            $this->dateTimesWithDelay();
        }
        return parent::afterFind();
    }

    public function generationDelay()
    {
        $this->generation_delay_in_seconds = $this->generation_delay*60;
    }

    public function dateTimesWithDelay()
    {
        $this->start_datetime_delayed = date('Y-m-d H:i:s',It::timeToUnixTimestamp($this->start_datetime) + $this->generation_delay_in_seconds);
        $this->next_run_planned_delayed = date('Y-m-d H:i:s',It::timeToUnixTimestamp($this->next_run_planned) + $this->generation_delay_in_seconds);
    }

    public function setTimes()
    {
        $nextTimeSeconds = ScheduleTypeReports::getNextPeriodTime($this->period*60);
        $this->next_run_planned  = date('Y-m-d H:i:s',$nextTimeSeconds);
        $lastTimeSeconds = ScheduleTypeReports::getLastPeriodTime($this->period*60);
        $this->start_datetime    = date('Y-m-d H:i:s',$lastTimeSeconds);
    }

    public function setTimeStep()
    {
        $this->parse_time = false;
        $nextTimeSeconds = ScheduleTypeReports::getNextPeriodTime( $this->period*60,It::timeToUnixTimestamp($this->next_run_planned));
        $this->next_run_planned    = date('Y-m-d H:i:s',$nextTimeSeconds);
        $nextTimeSeconds = ScheduleTypeReports::getNextPeriodTime($this->period*60,It::timeToUnixTimestamp($this->start_datetime));
        $this->start_datetime    = date('Y-m-d H:i:s',$nextTimeSeconds);
        $this->save(false);
    }

    public static function getStationTypes()
	{
//       return  Yii::app()->params['station_type'];
        return array(
            //'rain' => 'Rain',
            'aws'  => 'AWS',
          //  'awos' => 'AWOS'
        );
    }

    public static function getReportType()
	{
//        return  Yii::app()->params['schedule_report_type'];
       return array(
           //'bufr'        => 'BUFR',
           'ODSS'        => 'ODSS',
       );
    }

    public static function getPeriod()
	{
       return  Yii::app()->params['schedule_generation_period'];

    }

    public static function getReportFormat()
	{
       return  Yii::app()->params['schedule_report_format'];
    }

    public function getList($page_size = 15)
    {
        $criteria=new CDbCriteria();
        $criteria->with ='destinations';
        $count=$this->count($criteria);
        $pages=new CPagination($count);
        $pages->pageSize=$page_size;
        $pages->pageVar='page';
        $pages->applyLimit($criteria);
        $result = $this->findAll($criteria);

        return array('pages'=>$pages, 'result'=>$result);
    }




}