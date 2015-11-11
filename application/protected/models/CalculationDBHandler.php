<?php

class CalculationDBHandler extends CStubActiveRecord
{

    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public  function tableName()
	{
        return 'calculation_handler';
    }



    public function relations(){
        return array(
            'metric' => array(self::BELONGS_TO, 'RefbookMetric', 'metric_id')
        );
    }
	public static function getHandlers()
	{
        return CalculationDBHandler::model()->findAll(array('order' => 'display_name asc'));
    }
    public static function handlerWithFeatureAndMetric(&$handlers){
        $criteria = new CDbCriteria();
            $criteria->addCondition('t.aws_panel_show > 0');
            $criteria->with = array('metric');
            $criteria->order = "t.aws_panel_display_position";
            $criteria->index = 'handler_id';

        $handlers = self::model()->findAll($criteria);

        return array_keys($handlers);
    }

}

?>