<?php

class m150820_110723_WL_sensor extends CDbMigration
{
    protected $table_sensor_handler="sensor_handler";
    protected $table_refbook_measurement_type="refbook_measurement_type";
    protected $table_refbook_measurement_type_metric="refbook_measurement_type_metric";
    protected $table_sensor_handler_default_feature="sensor_handler_default_feature";
	public function up()
	{


//        INSERT INTO `sensor_handler` VALUES ('20', 'WaterLevel', 'Water Level', 'Water Level', 'WL', '0', '1', '1', 'water', '1', '0', '0', '25', '-1', '0000-00-00 00:00:00', '0000-00-00 00:00:00');


        $this->insert($this->table_sensor_handler, array(
            'handler_id_code'                   => 'WaterLevel',
            'display_name'                      => 'Water Level',
            'handler_default_display_name'      => 'Water Level',
            'default_prefix'                    => 'WL',
            'aws_panel_display_position'        => '0',
            'aws_panel_show'                    => '1',
            'aws_single_display_position'       => '1',
            'aws_single_group'                  => 'water',
            'aws_station_uses'                  => '1',
            'rain_station_uses'                 => '0',
            'awa_station_uses'                  => '0',
            'flags'                             => '25',
            'start_time'                        => '-1',
            'created'                           => '0000-00-00 00:00:00',
            'updated'                           => '0000-00-00 00:00:00',

        ));

        $this->insert($this->table_refbook_measurement_type, array(
            'display_name'          =>'Water Level',
            'code'                  =>'water_level',
            'ord'                   =>'20',

        ));

        $this->insert($this->table_refbook_measurement_type, array(
            'display_name'          =>'Water Level Offset',
            'code'                  =>'level_offset',
            'ord'                   =>'21',

        ));



        $this->insert($this->table_refbook_measurement_type_metric, array(
            'measurement_type_id'       =>'23',
            'metric_id'                 =>'4',
            'is_main'                   =>'0',

        ));

        $this->insert($this->table_refbook_measurement_type_metric, array(
            'measurement_type_id'       =>'23',
            'metric_id'                 =>'5',
            'is_main'                   =>'1',

        ));
        $this->insert($this->table_refbook_measurement_type_metric, array(
            'measurement_type_id'       =>'23',
            'metric_id'                 =>'11',
            'is_main'                   =>'0',

        ));
        $this->insert($this->table_refbook_measurement_type_metric, array(
            'measurement_type_id'       =>'24',
            'metric_id'                 =>'4',
            'is_main'                   =>'0',

        ));
        $this->insert($this->table_refbook_measurement_type_metric, array(
            'measurement_type_id'       =>'24',
            'metric_id'                 =>'5',
            'is_main'                   =>'1',

        ));
        $this->insert($this->table_refbook_measurement_type_metric, array(
            'measurement_type_id'       =>'24',
            'metric_id'                 =>'11',
            'is_main'                   =>'0',

        ));


        $this->insert($this->table_sensor_handler_default_feature, array(
            'handler_id'            =>'20',
            'feature_code'          =>'water_level',
            'aws_panel_show'        =>'1',
            'feature_constant_value'=>'0.00',
            'metric_id'             =>'11',
            'filter_max'            =>'0.00',
            'filter_min'            =>'0.00',
            'filter_diff'           =>'0.00',
            'created'               =>'0000-00-00 00:00:00',
            'updated'               =>'0000-00-00 00:00:00',
        ));
        $this->insert($this->table_sensor_handler_default_feature, array(
            'handler_id'            =>'20',
            'feature_code'          =>'level_offset',
            'aws_panel_show'        =>'0',
            'feature_constant_value'=>'0.00',
            'metric_id'             =>'11',
            'filter_max'            =>'0.00',
            'filter_min'            =>'0.00',
            'filter_diff'           =>'0.00',
            'created'               =>'0000-00-00 00:00:00',
            'updated'               =>'0000-00-00 00:00:00',
        ));
	}

	public function down()
	{
		echo "m150820_110723_WL_sensor does not support migration down.\n";
		return false;
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