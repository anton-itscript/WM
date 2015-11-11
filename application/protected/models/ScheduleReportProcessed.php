<?php

class ScheduleReportProcessed extends CStubActiveRecord
{
	public $report_string_initial;
	
    public static function model($className=__CLASS__)
	{
		return parent::model($className);
    }

    public function tableName() 
	{
		return 'schedule_report_processed';
    }

	public function relations()
    {
        return array(
            'ScheduleReportToStation' => array(self::BELONGS_TO, 'ScheduleReportToStation', 'sr_to_s_id'),
            'listenerLog' => array(self::BELONGS_TO, 'ListenerLog', 'listener_log_id'),
        );
    }

	protected function afterFind()
	{
		parent::afterFind();
//		if ($this->is_processed == 1){
//			$files_path = dirname(Yii::app()->request->scriptFile) . DIRECTORY_SEPARATOR ."files". DIRECTORY_SEPARATOR ."schedule_reports";
//			$this->report_string_initial = file_get_contents($files_path . DIRECTORY_SEPARATOR . $this->schedule_processed_id);
//		}
	}
	
    public function beforeSave()
	{
        if(!$this->getUseLong()){
            if ($this->isNewRecord)
            {
                $sql = "UPDATE `".ScheduleReportProcessed::model()->tableName()."` SET `is_last` = 0
                         WHERE `sr_to_s_id` = '".$this->sr_to_s_id."'
                         AND `is_last` = '1' ";
                Yii::app()->db->createCommand($sql)->query();

                $this->created = new CDbExpression('NOW()');
                $this->is_last = 1;
                $this->is_processed = 0;
            }
            else
            {
                if ($this->is_processed === 1)
                {
                    $sql = "SELECT `is_processed`
                            FROM `".ScheduleReportProcessed::model()->tableName()."`
                            WHERE `schedule_processed_id` = ?";

                    $res = Yii::app()->db->createCommand($sql)->queryScalar(array($this->schedule_processed_id));

                    if ($res == 0)
                    {
                        $sql = "UPDATE `".ScheduleReportProcessed::model()->tableName()."` SET `is_last` = 0
                         WHERE `sr_to_s_id` = '".$this->sr_to_s_id."'
                         AND `is_last` = '1' ";
                        Yii::app()->db->createCommand($sql)->query();
                        $this->is_last = 1;
                    }
                }
            }
            $this->updated = new CDbExpression('NOW()');
        }

        return parent::beforeSave();
    }
    
    public static function getHistory($schedule_id, $page_size = 15)
	{
        if ($page_size)
		{
            $sql = "SELECT COUNT(t1.schedule_processed_id)
                    FROM `".ScheduleReportProcessed::model()->tableName()."` t1
                    WHERE `t1`.`schedule_id` = '".$schedule_id."'";
			
            $total = Yii::app()->db->createCommand($sql)->queryScalar(); 
        }        
        
        if ($total || !$page_size)
		{
            if ($page_size)
			{
                $pages = new CPagination($total);
                $pages->pageSize = $page_size;
            }
            
            $sql = "SELECT 
						t1.*, t2.message, t2.measuring_timestamp, t3.report_type 
                    FROM 
						`".ScheduleReportProcessed::model()->tableName()."` t1
                    LEFT JOIN 
						`".ListenerLog::model()->tableName()."` t2 ON t2.log_id = t1.listener_log_id
                    LEFT JOIN 
						`".ScheduleReport::model()->tableName()."` t3 ON t3.schedule_id = t1.schedule_id
                    WHERE 
						`t1`.`schedule_id` = '".$schedule_id."'
                    ORDER BY 
						`t1`.`created` DESC";
			
            if ($page_size)
			{   
				$sql .= " LIMIT ".($pages->currentPage *$pages->pageSize).", ".$pages->pageSize;    
            }       
            
            $res = Yii::app()->db->createCommand($sql)->queryAll();
			
            if ($res)
			{
                $files_path = dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."schedule_reports";
                
				foreach ($res as $key => $value)
				{
                    if ($value['serialized_report_errors'])
					{
						$res[$key]['report_errors'] = unserialize($value['serialized_report_errors']);
                    }
					
                    if ($value['serialized_report_explanations'])
					{
						$res[$key]['report_explanations'] = unserialize($value['serialized_report_explanations']);
                    }
					
                    if (in_array($value['report_type'], array('synop', 'metar', 'speci')) && file_exists($files_path.DIRECTORY_SEPARATOR.$value['schedule_processed_id']))
					{
						$res[$key]['report_string_initial'] = file_get_contents($files_path.DIRECTORY_SEPARATOR.$value['schedule_processed_id']);
                    }
					
                    if ($value['listener_log_ids'])
					{
                        $sql = "SELECT t2.log_id, t2.message, t2.measuring_timestamp 
                                FROM  `". ListenerLog::model()->tableName() ."` t2
                                WHERE t2.log_id IN (". $value['listener_log_ids'] .")
                                ORDER BY `t2`.`measuring_timestamp` DESC";    
                        
						$res[$key]['logs'] = Yii::app()->db->createCommand($sql)->queryAll();
                    }
                }
            }
        }
		
        return array('list' => $res, 'pages' => $pages);    
    }
  
    public static function getInfoForRegenerate($id)
	{
		return ScheduleReportProcessed::model()->with('ScheduleReportToStation.schedule_report')->findByPk($id);
    }
}