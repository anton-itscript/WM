<?php


class HeartbeatReport extends CStubActiveRecord{
    public static function model($className=__CLASS__){
        return parent::model($className);
    }
    public function tableName(){
        return 'heartbeat_report';
    }
    public function beforeSave(){
        if(!$this->getUseLong()){
            if ($this->isNewRecord){
                $this->created = new CDbExpression('NOW()');
            }
            $this->updated = new CDbExpression('NOW()');
        }
        return parent::beforeSave();
    }

    /**
     * @param $period
     * @return array or string
     */
    public static function periodList($period){
        $periodList = array(
            'T' => 'DAY',
            'd' => 'DAY',
            'w' => 'WEEK',
            'm' => 'MONTH',
        );
        if(isset($periodList[$period]))
            return $periodList[$period];
        return $periodList;
    }

    /**
     * @param $status
     */
    public function status($status){
        $this->status = $status;
        if (!$this->isNewRecord){
            self::model()->updateByPk($this->report_id,array('status' => $this->status));
        }
    }
    /**
     * @param $ftp_status
     */
    public function ftpStatus($status){
        $this->ftp_status = $status;
        if (!$this->isNewRecord){
            self::model()->updateByPk($this->report_id,array('ftp_status' => $this->ftp_status));
        }
    }

    /**
     * @param $period
     * @return HeartbeatReport
     */
    public static function create($period){
        $report = new HeartbeatReport();
        $report->period = new CDbExpression('NOW()- INTERVAL 1 '.self::periodList($period));
        $report->status = 'create';
        $report->save();
        return $report->report_id;
    }

    /**
     * @param $pages
     * @return array
     */
    public static function getReportList(&$pages = null){
        $criteria = new CDbCriteria();
            $criteria->index = 'report_id';
            $criteria->order = 'created DESC';
        if(isset($pages)){
            $pages->setItemCount(self::model()->count($criteria));
            $pages->applyLimit($criteria);
        }
        return self::model()->findAll($criteria);
    }

    /**
     * @param $report_id
     * @return CStubActiveRecord
     */
    public static function getReport($report_id){
        return self::model()->findByPk($report_id);
    }
}