<?php

/**
 * Class StationSensorFeature
 *
 */
class StationSensorFeature extends CStubActiveRecord
{

    public $default;

    public $metrics_list;
    public $possible_constant_values;
    public $comment;


    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function beforeSave()
    {
        if (!$this->getUseLong()) {
            if ($this->isNewRecord) {
                $this->created = new CDbExpression('NOW()');
            }
            $this->updated = new CDbExpression('NOW()');
        }

        return parent::beforeSave();
    }

    public function tableName()
    {
        return 'station_sensor_feature';
    }

    public function rules()
    {
        return array(
            array('sensor_id', 'required'),
            array('feature_code,feature_display_name,measurement_type_code', 'length', 'allowEmpty' => false),
            array('metric_id', 'numerical', 'allowEmpty' => true, 'integerOnly' => true),
            array('filter_min,filter_max,filter_diff,feature_constant_value', 'numerical', 'allowEmpty' => true),
            array(
                'has_filter_min, has_filter_max, has_filter_diff,is_constant',
                'boolean', 'trueValue'  => 1, 'falseValue' => 0, 'allowEmpty' => false
            ),
        );
    }

    public function relations()
    {
        return array(
            'metric'         => array(self::BELONGS_TO, 'RefbookMetric', 'metric_id'),
            'sensor'         => array(self::BELONGS_TO, 'StationSensor', 'sensor_id'),
            'calculations'   => array(self::HAS_MANY, 'StationCalculationVariable', 'sensor_feature_id'),
            'sensor_data'    => array(self::HAS_MANY, 'SensorData', 'sensor_feature_id'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'filter_min'  => Yii::t('project', 'T1 <'),
            'filter_max'  => Yii::t('project', 'T1 >'),
            'filter_diff' => Yii::t('project', '|T0 - T1| >'),
        );
    }

    public static function getInfoForHandler($sensor_id)
    {
        $sql
             = "SELECT `t1`.*, `t2`.`code` AS `metric_code`, `t5`.`code` AS `general_metric_code`
                FROM `" . StationSensorFeature::model()->tableName() . "` `t1`
                LEFT JOIN `" . RefbookMetric::model()->tableName() . "`                `t2` ON `t2`.`metric_id` = `t1`.`metric_id`
                LEFT JOIN `" . RefbookMeasurementType::model()->tableName() . "`       `t3` ON `t3`.`code` = `t1`.`measurement_type_code`
                LEFT JOIN `" . RefbookMeasurementTypeMetric::model()->tableName() . "` `t4` ON `t4`.`measurement_type_id` = `t3`.`measurement_type_id` AND `t4`.`is_main` = 1
                LEFT JOIN `" . RefbookMetric::model()->tableName() . "`                `t5` ON `t5`.`metric_id` = `t4`.`metric_id`
                WHERE `t1`.`sensor_id` = ?";
        $arr = Yii::app()->db->createCommand($sql)->queryAll(true, array($sensor_id));

        foreach ($arr as $val) {
            $arr_key[] = $val['feature_code'];
        }

        return array_combine($arr_key, $arr);
    }

    public static function addSensorsFeaturesForStations(&$stations)
    {
        $station_ids = array_keys($stations);

        $criteria       = new CDbCriteria();
        $criteria->with = array('metric', 'sensor.handler');
        $criteria->compare('sensor.station_id', $station_ids);
        $criteria->order = "handler.aws_panel_display_position asc, sensor.sensor_id_code asc";

        $sensors_features = StationSensorFeature::model()->findAll($criteria);

        foreach ($sensors_features as $sensors_feature) {
            //sensor info
            $stations
            [$sensors_feature->sensor->station_id]
            ['sensors']
            [$sensors_feature->sensor->station_sensor_id]
            ['info']
                = $sensors_feature->sensor;
            //sensor handler
            $stations
            [$sensors_feature->sensor->station_id]
            ['sensors']
            [$sensors_feature->sensor->station_sensor_id]
            ['handler']
                = $sensors_feature->sensor->handler;
            //sensor feature info
            $stations
            [$sensors_feature->sensor->station_id]
            ['sensors']
            [$sensors_feature->sensor->station_sensor_id]
            ['feature']
            [$sensors_feature->sensor_feature_id]
            ['info']
                = $sensors_feature;
        }
    }

    public static function updateMetric()
    {
        $criteria        = new CDbCriteria;
        $criteria->index = 'code';
        $mainMetrics     = RefbookMeasurementType::model()->with('metricMain')->findAll($criteria);
        foreach ($mainMetrics as $code => $mainMetric) {
            StationSensorFeature::model()->updateAll(array(
                                                         'metric_id' => $mainMetric->metricMain->metric_id
                                                     ), 'measurement_type_code = :code', array(':code' => $code));
        }
    }

    /**
     * Update station_sensor_feature from sensor_handler_default_feature
     * Page Admin/SetupSensor
     *
     * @param $handler SensorDBHandler
     *                 Only for handler_id
     * @param $feature SensorDBHandlerDefaultFeature
     *
     * @return int
     */
    public static function updateByDefault($handler, $feature)
    {
        $qb = new CDbCriteria();
        $qb->with = [
            'sensor' => [
                'select'    => false,
                'condition' => "sensor.handler_id = {$handler->handler_id}"
            ]
        ];
        $qb->condition = "feature_code = '{$feature->feature_code}'";


        return StationSensorFeature::model()->updateAll(
            [
                'metric_id'              => $feature->metric_id,
                'filter_max'             => $feature->filter_max,
                'filter_min'             => $feature->filter_min,
                'filter_diff'            => $feature->filter_diff,
                'feature_constant_value' => $feature->feature_constant_value,
            ],
            $qb);

    }

}