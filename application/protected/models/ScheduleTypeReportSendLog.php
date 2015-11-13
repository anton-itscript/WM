<?php

class ScheduleTypeReportSendLog extends CStubActiveRecord
{
    public $send_logs_array = array();
    public static function model($className=__CLASS__)
	{
		return parent::model($className);
    }

    public function tableName()
	{
		return 'ex_schedule_send_log';
    }

    public function rules()
	{
        return array(
            array('ex_schedule_processed_id, ex_schedule_destination_id, sent, send_logs, updated, created', 'safe'),
        );
    }
	
	public function relations()
    {
        return array(
            'destination' => array(self::BELONGS_TO, 'ScheduleTypeReportDestination', 'ex_schedule_destination_id'),
            'report_processed' => array(self::BELONGS_TO, 'ScheduleTypeReportProcessed', 'ex_schedule_processed_id'),
        );
    }

    public function beforeSave()
	{
        if (!$this->getUseLong()) {
            if ($this->isNewRecord) {
                $this->created = date('Y-m-d H:i:s');
            }
            $this->updated = date('Y-m-d H:i:s');

        }
        return parent::beforeSave();
    }

    public function afterFind()
    {
        $this->transformSendLogs();
        return parent::afterFind();
    }

    /**
     * @param bool $synchronization_status
     * @return array
     */
    public function getUnsentReportItems($synchronization_status=false)
    {
        $criteria = new CDbCriteria();
        $criteria->alias = "t1";
        if($synchronization_status!==false) {

            $criteria->join = '
         LEFT JOIN `'.ScheduleTypeReportProcessed::model()->tableName(). '` `t2` on `t1`.`ex_schedule_processed_id`=`t2`.`ex_schedule_processed_id`';
            $criteria->addCondition('`t2`.`is_synchronized`= '.$synchronization_status);
        }
        $criteria->addCondition('`t1`.`sent`=0');
        $criteria->with = array('destination','report_processed.ex_schedule_report');
        $result = $this->findAll($criteria);
        if(!is_null($result)) {
            foreach ($result as & $item) {
                if (is_object($item->report_processed)){
                    $item->report_processed->setUniqueID($item->report_processed->ex_schedule_report->ex_schedule_ident);
                }
                if (is_object($item->destination)) {
                    $item->destination->getUid();
                }
            }

            $UIDsArray = $this->getUIDsArray($result);
            return array('result'=>$result, 'uids'=>$UIDsArray);
        }
        return array('result'=>array(), 'uids'=>array());
    }

    protected function getUIDsArray($result){

        $UIDsArray = array();
        foreach($result as $item){
            $reportUID ='';
            if (is_object($item->report_processed)){
                 $reportUID = $item->report_processed->unique_id;
            }
            $destinationUID='';
            if (is_object($item->destination)) {
                $destinationUID = $item->destination->getUid();
            }
            if (!empty($reportUID)) {
                $UIDsArray[$item->ex_schedule_processed_id]['report_uid'] = $reportUID ;
                $UIDsArray[$item->ex_schedule_processed_id]['destination_uids'][] = $destinationUID ;
            }
        }
        return $UIDsArray;
    }


    /**
     * @param $uids array
     * @param bool $sent true| false
     * @return array
     */
    public function findByUIDsWithDestinations($uids, $sent=false)
    {
        $criteria = new CDbCriteria();
        $criteria->alias = "t1";
        $criteria->join = '
         LEFT JOIN `'.ScheduleTypeReportProcessed::model()->tableName(). '` `t2` on `t1`.`ex_schedule_processed_id`=`t2`.`ex_schedule_processed_id`
         LEFT JOIN `'.ScheduleTypeReport::model()->tableName(). '` `t3` on `t2`.`ex_schedule_id`=`t3`.`ex_schedule_id`
        ' ;

        $criteria->with = array('report_processed.ex_schedule_report', 'destination');
        if ($sent == false) {
            $sent_condition = ' AND `t1`.`sent`="0"';
        }
        if ($sent == true) {
            $sent_condition = ' AND `t1`.`sent`="1"';
        }
        if (count($uids)) {
            foreach ($uids as $uid) {
                $res = ScheduleTypeReportProcessed::parseUID($uid['report_uid']);
                $criteria->addCondition('(`t2`.`check_period_start`="' . $res['check_period_start'] . '"
                                    AND `t3`.`ex_schedule_ident`="' . $res['ex_schedule_ident'] . '"
                                    ' . $sent_condition . ')',
                    'OR');
            }

            $result = $this->findAll($criteria);
            if (!is_null($result)) {
                foreach ($result as & $item) {
                    if (is_object($item->report_processed->ex_schedule_report) && is_object($item->report_processed)) {
                        $item->report_processed->setUniqueID($item->report_processed->ex_schedule_report->ex_schedule_ident);
                    }
                }
                $UIDsArray = $this->getUIDsArray($result);
                return array('result' => $result, 'uids' => $UIDsArray);
            }
        }
        return array('result'=>array(), 'uids'=>array());
    }


    protected function transformSendLogs() {
        $sl = unserialize($this->send_logs);

        if($sl!=false) {
            $this->send_logs_array = $sl;
        } else {
            $this->send_logs_array = array();
        }
    }

    public static function isExist($ex_schedule_processed_id,$ex_schedule_destination_id)
    {
        $criteria = new CDbCriteria();
        $criteria->alias = "t1";
        $criteria->addCondition('`t1`.`ex_schedule_processed_id`='.$ex_schedule_processed_id.' AND `t1`.`ex_schedule_destination_id`="'.$ex_schedule_destination_id.'"');
        $result = self::model()->find($criteria);
        if(!is_null($result)) {
            return $result;
        }
        return false;
    }



    public function getList($page_size = 15)
    {
        $criteria=new CDbCriteria();
//        $criteria->with ='destinations';
        $count=$this->count($criteria);
        $pages=new CPagination($count);
        $pages->pageSize=$page_size;
        $pages->pageVar='page';
        $pages->applyLimit($criteria);
        $result = $this->findAll($criteria);

        return array('pages'=>$pages, 'result'=>$result);
    }




}