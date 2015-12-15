<?php

class m151202_072738_temperature_sensors_feature_fix extends CDbMigration
{
	public function up()
	{
		echo "ATTENTION this migration fix only station_sensor_feature table \r\n";
		echo "ATTENTION this migration fix only station_sensor_feature table \r\n";
		echo "ATTENTION this migration fix only station_sensor_feature table \r\n";
		echo "ATTENTION this migration fix only station_sensor_feature table \r\n";

		$handler = new SensorDBHandler;
		$sensors = $handler->with('sensors.features')->findByAttributes(array('handler_id_code'=>'TemperatureSoil'));

		if (!is_null($sensors['sensors'])) {
			foreach ($sensors['sensors'] as $sensor) {
				if (is_array($sensor['features'])) {
					foreach ($sensor['features'] as $feature ) {
						if ($feature->is_main) {
							$feature->setAttribute('feature_code','temperature_soil');
							$feature->setAttribute('measurement_type_code','temperature_soil');
							$feature->save(false);
						}
					}
				}
			}
		}

		$handler = new SensorDBHandler;
		$sensors = $handler->with('sensors.features')->findByAttributes(array('handler_id_code'=>'TemperatureWater'));

		if (!is_null($sensors['sensors'])) {
			foreach ($sensors['sensors'] as $sensor) {
				if (is_array($sensor['features'])) {
					foreach ($sensor['features'] as $feature ) {
						if ($feature->is_main) {
							$feature->setAttribute('feature_code','temperature_water');
							$feature->setAttribute('measurement_type_code','temperature_water');
							$feature->save(false);
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