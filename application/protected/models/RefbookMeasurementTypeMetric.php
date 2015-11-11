<?php

class RefbookMeasurementTypeMetric extends CStubActiveRecord
{

    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public  function tableName()
	{
        return 'refbook_measurement_type_metric';
    }
	
	public function relations()
    {
        return array(
            'measurement_type' => array(self::BELONGS_TO, 'RefbookMeasurementType', 'measurement_type_id'),
			'metric' => array(self::BELONGS_TO, 'RefbookMetric', 'metric_id'),
        );
    }

    public static function getMetrics($measurement_type_code)
	{
        $return = array();
        
		$sql = "SELECT `t1`.`metric_id`, `t3`.`html_code`, `t3`.`full_name`
                FROM `".RefbookMeasurementTypeMetric::model()->tableName()."` `t1`
                LEFT JOIN `".RefbookMeasurementType::model()->tableName()."`  `t2` ON `t2`.`measurement_type_id` = `t1`.`measurement_type_id`
                LEFT JOIN `".RefbookMetric::model()->tableName()."`           `t3` ON `t3`.`metric_id` = `t1`.`metric_id`
                WHERE `t2`.`code` = '".$measurement_type_code."'    
                ORDER BY `t3`.`full_name`";
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        
		if ($res)
		{
            foreach ($res as $key => $value)
			{
				$return[$value['metric_id']] = $value['html_code'].' ('.$value['full_name'].')';
            }
        }
        return $return;
    }
}