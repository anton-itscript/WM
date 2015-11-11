<?php

class SensorDBHandlerDefaultFeature extends CStubActiveRecord {

    var $feature_display_name;
    var $measurement_type_code;
    var $is_cumulative;
    var $has_filter_min;
    var $has_filter_max;
    var $has_filter_diff;
    var $metrics_list;
    var $is_constant;
    var $comment;


    public static function model($className=__CLASS__){
        return parent::model($className);
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

    public function tableName() {
        return 'sensor_handler_default_feature';
    }
    public function relations(){
        return array(
            'metric' => array(self::BELONGS_TO, 'RefbookMetric', 'metric_id')
        );
    }

    public function rules() {
        return array(
            array('feature_code', 'length', 'allowEmpty' => false),
            array('metric_id', 'numerical', 'allowEmpty' => true, 'integerOnly' => true),
            array('filter_min,filter_max,filter_diff,feature_constant_value, aws_panel_show', 'numerical', 'allowEmpty' => true),
        );
    }    
    
    public function attributeLabels() {
        return array (
            'filter_min' => Yii::t('project', 'T1 <'),
            'filter_max' => Yii::t('project', 'T1 >'),
            'filter_diff' => Yii::t('project', '|T0 - T1| >'),
        );
    }
    
    
}

?>