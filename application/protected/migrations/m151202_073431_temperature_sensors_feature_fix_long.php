<?php

class m151202_073431_temperature_sensors_feature_fix_long extends CDbMigration
{
//	public function getDbConnection()
//	{
//
//		$this->_db = Yii::app()->db_long;
//		return $this->_db;
//	}
//
//	public function setDbConnection($db)
//	{
//		$this->_db = $db;
//	}

	public function up()
	{

		echo "ATTENTION this migration fix only station_sensor_feature table \r\n";
		echo "ATTENTION this migration fix only station_sensor_feature table \r\n";
		echo "ATTENTION this migration fix only station_sensor_feature table \r\n";
		echo "ATTENTION this migration fix only station_sensor_feature table \r\n";

		$handler = new SensorDBHandler;
		$sensors = $handler->long()->with('sensors.features')->findByAttributes(array('handler_id_code'=>'TemperatureSoil'));

		if (!is_null($sensors['sensors'])) {
			foreach ($sensors['sensors'] as $sensor) {
				if (is_array($sensor['features'])) {
					foreach ($sensor['features'] as $feature ) {
						if ($feature->is_main) {
							$feature->long()->setAttribute('feature_code','temperature_soil');
							$feature->long()->setAttribute('measurement_type_code','temperature_soil');
							$feature->long()->save(false);
						}
					}
				}
			}
		}

		$handler = new SensorDBHandler;
		$sensors = $handler->long()->with('sensors.features')->findByAttributes(array('handler_id_code'=>'TemperatureWater'));

		if (!is_null($sensors['sensors'])) {
			foreach ($sensors['sensors'] as $sensor) {
				if (is_array($sensor['features'])) {
					foreach ($sensor['features'] as $feature ) {
						if ($feature->is_main) {
							$feature->long()->setAttribute('feature_code','temperature_water');
							$feature->long()->setAttribute('measurement_type_code','temperature_water');
							$feature->long()->save(false);
						}
					}
				}
			}
		}



	}

	public function down()
	{
		return true;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}