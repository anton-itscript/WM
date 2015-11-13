<?php

class ScheduleTypeReportProcessed extends CStubActiveRecord
{
    /**
     * is_synchronized
     * 0 was not synchronized
     * 1 was synchronized
     * 2 could not synchronized ( in another side does not has uid like this)
     */




	public $report_string_initial;
	public $pk_after_save;
	public $unique_id;
	public $unix_timestamp;

    protected $file_dir;
    public $file_content;
    public $file_name;
    public $full_file_name;

    public function init()
    {
        parent::init();
        $this->file_dir = dirname(Yii::app()->request->scriptFile) .
            DIRECTORY_SEPARATOR . ".." .
            DIRECTORY_SEPARATOR . "www" .
            DIRECTORY_SEPARATOR . "files" .
            DIRECTORY_SEPARATOR . "schedule_type_reports";
        if (!is_dir($this->file_dir)) {
            @mkdir($this->file_dir, 0777, true);
        }
    }
    public function AfterFind()
    {
        parent::AfterFind();
            $this->unix_timestamp = It::timeToUnixTimestamp($this->check_period_start);
            $this->setFileContent();

    }

    public function beforeDelete()
    {
        if (is_file( $this->file_dir . DIRECTORY_SEPARATOR . $this->ex_schedule_processed_id)) {
                unlink($this->file_dir . DIRECTORY_SEPARATOR . $this->ex_schedule_processed_id);
        }
        return parent::beforeDelete();
    }

    public function getFileDir()
    {
        return $this->file_dir;
    }

    public function getFileContent()
    {
        return file_get_contents( $this->file_dir . DIRECTORY_SEPARATOR . $this->ex_schedule_processed_id );
    }

    public function setFileContent()
    {
         if (is_file( $this->file_dir . DIRECTORY_SEPARATOR . $this->ex_schedule_processed_id)) {
             $this->file_content = file_get_contents( $this->file_dir . DIRECTORY_SEPARATOR . $this->ex_schedule_processed_id );
             $this->file_name = $this->ex_schedule_processed_id;
             $this->full_file_name = $this->file_dir . DIRECTORY_SEPARATOR . $this->ex_schedule_processed_id;

         }
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
    }

    public function tableName() 
	{
		return 'ex_schedule_report_processed';
    }

	public function relations()
    {
        return array(
            'ex_schedule_report' => array(self::BELONGS_TO, 'ScheduleTypeReport', 'ex_schedule_id'),
            'send_log' => array(self::HAS_MANY, 'ScheduleTypeReportSendLog', 'ex_schedule_processed_id'),
//            'ex_schedule_report' => array(self::HAS_MANY, 'ScheduleTypeReport', 'ex_schedule_id'),

        );
    }

    public function beforeSave()
	{
        if(!$this->getUseLong()){
            if ($this->isNewRecord) {
                $this->created = new CDbExpression('NOW()');
            }
            $this->updated = new CDbExpression('NOW()');

            $synchronization = new Synchronization();
            if ($synchronization->isProcessed()) {
                if ($synchronization->isMaster()) {
                    $this->current_role = 'master';
                } else {
                    $this->current_role = 'slave';
                }
            }
        }
        return parent::beforeSave();
    }

    public function afterSave()
	{
        $this->pk_after_save = $this->ex_schedule_processed_id;
        $this->setFileContent();
        $this->file_name = $this->ex_schedule_processed_id;
        $this->full_file_name = $this->file_dir . DIRECTORY_SEPARATOR . $this->ex_schedule_processed_id;

        return parent::afterSave();
    }
    //??
    public function getSavedPk()
    {
        return $this->pk_after_save;
    }

    public static function getHistory($schedule_id, $page_size = 15)
	{
        $criteria=new CDbCriteria();
        $criteria->alias = "t1";
        $criteria->condition = "t1.ex_schedule_id = $schedule_id";
        $criteria->order ="check_period_start DESC";
        $criteria->with = array('send_log.destination');
        $strp = new ScheduleTypeReportProcessed;

        $count=$strp->count($criteria);
        $pages=new CPagination($count);
        $pages->pageSize=$page_size;
        $pages->pageVar='page';
        $pages->applyLimit($criteria);

        $historyResult = $strp->findAll($criteria);

        $historyArrResult = array();
        if (!is_null($historyResult)) {
           foreach ($historyResult as $historyItem) {
               $historyArrResult[] = $historyItem->attributes;

           }
        }
        return array('pages'=>$pages, 'result'=>$historyResult);
    }
  
    public static function getInfoForRegenerate($id)
	{
		//return ScheduleReportProcessed::model()->with('ScheduleReportToStation.schedule_report')->findByPk($id);
    }












    public function setSent()
    {
        $this->sent = 1;
        $this->save(false);
        return true;
    }
    public function isSent()
    {
        if ($this->sent) {
            return true;
        }
        return false;
    }

    public function setRoleMaster()
    {
            $this->current_role = 'master';
            $this->save(false);
            return true;
    }
    public function setRoleSlave()
    {
            $this->current_role = 'slave';
            $this->save(false);
            return true;
    }


    public function getRole()
    {
        return $this->current_role;
    }

    public function isRoleMaster()
    {
        if ($this->current_role=='master') {
            return true;
        }
        return false;
    }

    public function isRoleSlave()
    {
        if ($this->current_role=='slave') {
            return true;
        }
        return false;
    }

    public function isRoleNone()
    {
        if ($this->current_role=='none') {
            return true;
        }
        return false;
    }

    /**
     * @return array('result'=>array,'uids')
     */
//    public function getUnsentReports()
//    {
//        $criteria = new CDbCriteria();
//        $criteria->addCondition('sent=0');
//        $criteria->with = array('ex_schedule_report');
//        $result = $this->findAll($criteria);
//        if(!is_null($result)) {
//            foreach ($result as & $item) {
//                if (is_object($item->ex_schedule_report)){
//                    $item->setUniqueID($item->ex_schedule_report->ex_schedule_ident);
//                }
//            }
//            $UIDsArray = $this->getUIDsArray($result);
//            return array('result'=>$result, 'uids'=>$UIDsArray);
//        }
//        return array('result'=>array(), 'uids'=>array());
//    }

    protected function getUIDsArray($result){

        $UIDsArray = array();
        foreach($result as $item){
            $UIDsArray[$item->ex_schedule_processed_id] = $item->unique_id;
        }

        return $UIDsArray;
    }

    public function setUniqueID($reportIdentifier)
    {
        $this->unique_id = $this->unix_timestamp."_".$reportIdentifier;
    }

    /**
     * @param $uids
     * @param int $is_synchronized
     * @return array
     */
    public function findByUIDs($uids, $is_synchronized=0)
    {
        if (count($uids)) {
            $criteria = new CDbCriteria();
            $criteria->alias = "t1";
            $criteria->join = 'LEFT JOIN ' . ScheduleTypeReport::model()->tableName() . ' as  `t2` on `t1`.`ex_schedule_id`=`t2`.`ex_schedule_id`';
            $criteria->with = array('ex_schedule_report');

            $synchronized_condition = " AND `t1`.`is_synchronized`=" . $is_synchronized;

            foreach ($uids as $uid) {
                $res = self::parseUID($uid);
                $criteria->addCondition('`t1`.`check_period_start`="' . $res['check_period_start'] . '"
             AND `t2`.`ex_schedule_ident`="' . $res['ex_schedule_ident'] . '" ' . $synchronized_condition, 'OR');
            }

            $result = $this->findAll($criteria);

            if (!is_null($result)) {
                foreach ($result as & $item) {
                    if (is_object($item->ex_schedule_report)) {
                        $item->setUniqueID($item->ex_schedule_report->ex_schedule_ident);
                    }
                }
                $UIDsArray = $this->getUIDsArray($result);
                return array('result' => $result, 'uids' => $UIDsArray);
            }
        }
        return array('result'=>array(), 'uids'=>array());
    }

    public static function parseUID($uid)
    {
        $res = explode("_",$uid);
        $result['check_period_start'] = It::UnixTimestampToTime($res[0]);
        $result['ex_schedule_ident'] = $res[1];
        return $result;
    }


    public function findByUIDsWithDestinations($uids)
    {
        $criteria = new CDbCriteria();
        $criteria->alias = "t1";
        $criteria->join = 'LEFT JOIN `'.ScheduleTypeReport::model()->tableName(). '` `t2` on `t1`.`ex_schedule_id`=`t2`.`ex_schedule_id`' ;
        $criteria->with = array('ex_schedule_report', 'send_log.destination');

        foreach ($uids as $uid) {
            $res = self::parseUID($uid);
            $criteria->addCondition('(`t1`.`check_period_start`="'.$res['check_period_start'].'" AND `t2`.`ex_schedule_ident`="'.$res['ex_schedule_ident'].'")','OR');
        }

        $result = $this->findAll($criteria);

        if(!is_null($result)) {
            foreach ($result as & $item) {
                if (is_object($item->ex_schedule_report)){
                    $item->setUniqueID($item->ex_schedule_report->ex_schedule_ident);
                }
            }
            $UIDsArray = $this->getUIDsArray($result);
            return array('result'=>$result, 'uids'=>$UIDsArray);
        }

        return array('result'=>array(), 'uids'=>array());
    }


    public function getFileName($scheduleReportObject)
    {
        $file_name = $this->check_period_start
            . '-' . $this->check_period_end
            . '.' . $scheduleReportObject->report_format;

        return  $file_name;
    }

    public function isItemExists($ex_schedule_id,$check_period_start){

        $criteria = new CDbCriteria();
        $criteria->alias = "t1";
        $criteria->addCondition('`t1`.`ex_schedule_id`='.$ex_schedule_id.' AND `t1`.`check_period_start`="'.$check_period_start.'"');
        $result = $this->find($criteria);
        if(!is_null($result)) {
            return $result;
        }
        return false;
    }
}