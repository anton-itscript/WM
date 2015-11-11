<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 20.05.2015
 * Time: 17:40
 */

class GenerateMessageCommand  extends CConsoleCommand
{
    protected $_logger = null;
    /**
     * @var $stationForm configForm
     * **/

    public function run($args)
    {
//        error_reporting(null);
        ini_set('display_errors', 1);

        $this->_logger = LoggerFactory::getFileLogger('GenerateMessageCommand');
//        $this->_logger = LoggerFactory::getConsoleLogger();
        $this->_logger->log('start');

//       $args:
//       $args[0] AWS01
//       $args[1] "sensors:TS1;TS2;"

        $station_id_code = false;
        if (preg_match('/^([A-Z,a-z,0-9]{4,5})$/', $args[0], $matchesStations)) {
            $station_id_code = $matchesStations[1];
        } else {
            $this->_logger->log(' Station ID can contain only Letters (A-Z) and Figures (1-9), and must be of 4(rain) or 5(AWS) chars length.');
        }
        $sensor_id_codes = array();
        if (isset($args[1])) {
            if (preg_match("/^sensors:([A-Za-z1-9;]+)/", $args[1], $matchesSensors)) {
                $sensor_id_code = explode(';', $matchesSensors[1]);

                $sensor_id_codes = array_values($sensor_id_code);
                $sensor_id_codes_count = count($sensor_id_codes);
                for ($i = 0; $i < $sensor_id_codes_count; $i++) {
                    if (!preg_match('/^([A-Z,a-z]{2})([1-9]{1})$/', $sensor_id_codes[$i], $matches)) {
                        unset($sensor_id_codes[$i]);
                        $this->_logger->log('Sensor ID should contain two letters and 1 digit. Ex.: TP1');
                    }
                }

                $sensor_id_codes = array_values($sensor_id_codes);
            }
        }
        $station = Station::getStationByCode($station_id_code,array('sensors.handler','sensors.features.metric'));

//            $sql = "SELECT `t1`.`station_sensor_id`, `t1`.`sensor_id_code`,  `t2`.`handler_id_code`, `t3`.`feature_code`, `t4`.`code` AS `metric_code`
//                            FROM `".StationSensor::model()->tableName()."` `t1`
//                            LEFT JOIN `".SensorDBHandler::model()->tableName()."`      `t2` ON `t2`.`handler_id` = `t1`.`handler_id`
//                            LEFT JOIN `".StationSensorFeature::model()->tableName()."` `t3` ON `t3`.`sensor_id`  = `t1`.`station_sensor_id`
//                            LEFT JOIN `".RefbookMetric::model()->tableName()."`        `t4` ON `t4`.`metric_id`  = `t3`.`metric_id`
//                            WHERE `t1`.`station_id` = '".$station_id."' AND `t1`.`station_sensor_id` IN (".implode(',',$sensor_id).")";
//            $res = Yii::app()->db->createCommand($sql)->queryAll();

        if ($station) {
            TimezoneWork::set($station->timezone_id);
            $sensors = array();
            foreach ($station->sensors as $key => $sensor) {
                if (in_array($sensor->sensor_id_code, $sensor_id_codes) || count($sensor_id_codes) == 0) {
                    if (!isset($sensors[$sensor->station_sensor_id])) {
                        $sensors[$sensor->station_sensor_id] = array(
                            'station_sensor_id' => $sensor->station_sensor_id,
                            'sensor_id_code' => $sensor->sensor_id_code,
                            'handler_id_code' => $sensor->handler->handler_id_code
                        );
                    }
                    foreach ($sensor->features as $feature) {
                        if (is_object($feature->metric))
                            $sensors[$sensor->station_sensor_id]['features'][$feature->feature_code] = $feature->metric->code;
                    }
                }
            }

            $i = time();
            $messages[$i]['timestamp'] = $i;
            $this->_logger->log(__METHOD__ . ': sensors ' . print_r($sensors['sensor_id_code'], 1));
            foreach ($messages as $key => $value) {
                if ($station->station_type === 'rain') {
                    $messages[$key]['parts'][] = 'D';
                    $messages[$key]['parts'][] = $station->station_id_code;
                    $messages[$key]['parts'][] = date('ymd', $key);
                    $messages[$key]['parts'][] = date('Hi', $key);
                    $messages[$key]['parts'][] = str_pad(rand(100, 135), 3, "0", STR_PAD_LEFT);
                    $messages[$key]['parts'][] = '00';
                } else {
                    $messages[$key]['parts'][] = 'D';
                    $messages[$key]['parts'][] = $station->station_id_code;
                    $messages[$key]['parts'][] = date('ymd', $key);
                    $messages[$key]['parts'][] = date('Hi', $key);
                    $messages[$key]['parts'][] = '00';
                }

                $sensors_values = array();

                if ($sensors) {
                    foreach ($sensors as $k1 => $v1) {
                        $handler = SensorHandler::create($v1['handler_id_code']);

                        $random_value = $handler->getRandomValue($v1['features']);
                        $sensors_values[] = $v1['sensor_id_code'] . $random_value;
                    }

                    shuffle($sensors_values);
                    foreach ($sensors_values as $k1 => $v1) {
                        $messages[$key]['parts'][] = $v1;
                    }
                }
                $crc = It::prepareCRC(implode('', $messages[$key]['parts']));
                $messages[$key]['parts'][] = $crc;

                array_push($messages[$key]['parts'], '$');
                array_unshift($messages[$key]['parts'], '@');
            }
            $messages_display = array();
            $messages_copy = array();
            foreach ($messages as $key => $value) {
                $messages_display[] = implode(' ', $value['parts']);
                $messages_copy[] = implode('', $value['parts']);
            }
            $this->_logger->log(__METHOD__ . ': $messages_copy ' . print_r($messages_copy, 1));
            foreach ($messages_copy as $msg) {
                ListenerLogTemp::addNew($msg, 0, 1, 'import', 0);
            }
        } else {
            $this->_logger->log(__METHOD__ . ': has no stations like '.$args[0] );
        }

    }
}