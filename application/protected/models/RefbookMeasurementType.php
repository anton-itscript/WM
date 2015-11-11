<?php

class RefbookMeasurementType extends CStubActiveRecord {

    var $metrics_list;
    var $main_metric_id;


    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName() {
        return 'refbook_measurement_type';
    }

    public function relations(){
        return array(
            'metricMain' => array(self::HAS_ONE, 'RefbookMeasurementTypeMetric', 'measurement_type_id',
                'condition' => 'metricMain.is_main = 1')
        );
    }

}