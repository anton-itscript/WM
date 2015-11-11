<?php

/**
 * Description of CloudHeightAwsDlm13mSensorHandlerTest
 *
 * @author
 */
class CloudHeightAwsDlm13mSensorHandlerTest extends CTestCase
{
	public function setUp() 
	{
		error_reporting(E_ALL ^ E_NOTICE);
	} 
	
	public function test_PrepareDataPairs_WrongSize()
	{
		$handler = new CloudHeightAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '1';
		
		$this->assertFalse($handler->_prepareDataPairs());
	}
	
	public function test_PrepareDataPairs_WithoutCloudAmount()
	{
		$handler = new CloudHeightAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = 's66551234567890543210987632154879601000050000';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'status',
				'period' => 1,
				'value' => '6655',
				'metric_id' => null,
				'normilized_value' => '6655',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_height_1',
				'period' => 1,
				'value' => '12345',
				'metric_id' => null,
				'normilized_value' => '12345',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_depth_1',
				'period' => 1,
				'value' => '67890',
				'metric_id' => null,
				'normilized_value' => '67890',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_height_2',
				'period' => 1,
				'value' => '54321',
				'metric_id' => null,
				'normilized_value' => '54321',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_depth_2',
				'period' => 1,
				'value' => '09876',
				'metric_id' => null,
				'normilized_value' => '09876',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_height_3',
				'period' => 1,
				'value' => '32154',
				'metric_id' => null,
				'normilized_value' => '32154',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_depth_3',
				'period' => 1,
				'value' => '87960',
				'metric_id' => null,
				'normilized_value' => '87960',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_vertical_visibility',
				'period' => 1,
				'value' => '10000',
				'metric_id' => null,
				'normilized_value' => '10000',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_measuring_range',
				'period' => 1,
				'value' => '50000',
				'metric_id' => null,
				'normilized_value' => '50000',
				'is_m' => 0,
			),
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
	
	public function test_PrepareDataPairs_EmptyValues()
	{
		$handler = new CloudHeightAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = 'MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'cloud_height_height_1',
				'period' => 1,
				'value' => 'MMMMM',
				'metric_id' => null,
				'normilized_value' => 'MMMMM',
				'is_m' => 1,
			),
			array(
				'feature_code' => 'cloud_height_depth_1',
				'period' => 1,
				'value' => 'MMMMM',
				'metric_id' => null,
				'normilized_value' => 'MMMMM',
				'is_m' => 1,
			),
			array(
				'feature_code' => 'cloud_height_height_2',
				'period' => 1,
				'value' => 'MMMMM',
				'metric_id' => null,
				'normilized_value' => 'MMMMM',
				'is_m' => 1,
			),
			array(
				'feature_code' => 'cloud_height_depth_2',
				'period' => 1,
				'value' => 'MMMMM',
				'metric_id' => null,
				'normilized_value' => 'MMMMM',
				'is_m' => 1,
			),
			array(
				'feature_code' => 'cloud_height_height_3',
				'period' => 1,
				'value' => 'MMMMM',
				'metric_id' => null,
				'normilized_value' => 'MMMMM',
				'is_m' => 1,
			),
			array(
				'feature_code' => 'cloud_height_depth_3',
				'period' => 1,
				'value' => 'MMMMM',
				'metric_id' => null,
				'normilized_value' => 'MMMMM',
				'is_m' => 1,
			),
			array(
				'feature_code' => 'cloud_vertical_visibility',
				'period' => 1,
				'value' => 'MMMMM',
				'metric_id' => null,
				'normilized_value' => 'MMMMM',
				'is_m' => 1,
			),
			array(
				'feature_code' => 'cloud_measuring_range',
				'period' => 1,
				'value' => 'MMMMM',
				'metric_id' => null,
				'normilized_value' => 'MMMMM',
				'is_m' => 1,
			),
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
	
	public function test_PrepareDataPairs_WithCloudAmounts()
	{
		$handler = new CloudHeightAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = 's66551234567890543210987632154879601000050000-100350,260350,304350,520353,8-';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'status',
				'period' => 1,
				'value' => '6655',
				'metric_id' => null,
				'normilized_value' => '6655',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_height_1',
				'period' => 1,
				'value' => '12345',
				'metric_id' => null,
				'normilized_value' => '12345',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_depth_1',
				'period' => 1,
				'value' => '67890',
				'metric_id' => null,
				'normilized_value' => '67890',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_height_2',
				'period' => 1,
				'value' => '54321',
				'metric_id' => null,
				'normilized_value' => '54321',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_depth_2',
				'period' => 1,
				'value' => '09876',
				'metric_id' => null,
				'normilized_value' => '09876',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_height_3',
				'period' => 1,
				'value' => '32154',
				'metric_id' => null,
				'normilized_value' => '32154',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_depth_3',
				'period' => 1,
				'value' => '87960',
				'metric_id' => null,
				'normilized_value' => '87960',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_vertical_visibility',
				'period' => 1,
				'value' => '10000',
				'metric_id' => null,
				'normilized_value' => '10000',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_measuring_range',
				'period' => 1,
				'value' => '50000',
				'metric_id' => null,
				'normilized_value' => '50000',
				'is_m' => 0,
			),
			
			array(
				'feature_code' => 'cloud_amount_amount_1',
				'period' => 30,
				'value' => '1',
				'metric_id' => null,
				'normilized_value' => '1',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_amount_height_1',
				'period' => 30,
				'value' => '350',
				'metric_id' => null,
				'normilized_value' => '350',
				'is_m' => 0,
			),
			
			array(
				'feature_code' => 'cloud_amount_amount_2',
				'period' => 30,
				'value' => '2',
				'metric_id' => null,
				'normilized_value' => '2',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_amount_height_2',
				'period' => 30,
				'value' => '60350',
				'metric_id' => null,
				'normilized_value' => '60350',
				'is_m' => 0,
			),
			
			array(
				'feature_code' => 'cloud_amount_amount_3',
				'period' => 30,
				'value' => '3',
				'metric_id' => null,
				'normilized_value' => '3',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_amount_height_3',
				'period' => 30,
				'value' => '04350',
				'metric_id' => null,
				'normilized_value' => '04350',
				'is_m' => 0,
			),
			
			array(
				'feature_code' => 'cloud_amount_amount_4',
				'period' => 30,
				'value' => '5',
				'metric_id' => null,
				'normilized_value' => '5',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_amount_height_4',
				'period' => 30,
				'value' => '20353',
				'metric_id' => null,
				'normilized_value' => '20353',
				'is_m' => 0,
			),
			
			array(
				'feature_code' => 'cloud_amount_amount_total',
				'period' => 30,
				'value' => '8',
				'metric_id' => null,
				'normilized_value' => '8',
				'is_m' => 0,
			),
			
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
	
	public function test_PrepareDataPairs_WithCloudAmounts_SomeEmptyValues()
	{
		$handler = new CloudHeightAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = 's66551234567890543210987632154879601000050000-100350,//////,304350,//////,7-';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'status',
				'period' => 1,
				'value' => '6655',
				'metric_id' => null,
				'normilized_value' => '6655',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_height_1',
				'period' => 1,
				'value' => '12345',
				'metric_id' => null,
				'normilized_value' => '12345',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_depth_1',
				'period' => 1,
				'value' => '67890',
				'metric_id' => null,
				'normilized_value' => '67890',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_height_2',
				'period' => 1,
				'value' => '54321',
				'metric_id' => null,
				'normilized_value' => '54321',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_depth_2',
				'period' => 1,
				'value' => '09876',
				'metric_id' => null,
				'normilized_value' => '09876',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_height_3',
				'period' => 1,
				'value' => '32154',
				'metric_id' => null,
				'normilized_value' => '32154',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_height_depth_3',
				'period' => 1,
				'value' => '87960',
				'metric_id' => null,
				'normilized_value' => '87960',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_vertical_visibility',
				'period' => 1,
				'value' => '10000',
				'metric_id' => null,
				'normilized_value' => '10000',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_measuring_range',
				'period' => 1,
				'value' => '50000',
				'metric_id' => null,
				'normilized_value' => '50000',
				'is_m' => 0,
			),
			
			array(
				'feature_code' => 'cloud_amount_amount_1',
				'period' => 30,
				'value' => '1',
				'metric_id' => null,
				'normilized_value' => '1',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_amount_height_1',
				'period' => 30,
				'value' => '350',
				'metric_id' => null,
				'normilized_value' => '350',
				'is_m' => 0,
			),
			
			array(
				'feature_code' => 'cloud_amount_amount_2',
				'period' => 30,
				'value' => '',
				'metric_id' => null,
				'normilized_value' => '',
				'is_m' => 1,
			),
			array(
				'feature_code' => 'cloud_amount_height_2',
				'period' => 30,
				'value' => '',
				'metric_id' => null,
				'normilized_value' => '',
				'is_m' => 1,
			),
			
			array(
				'feature_code' => 'cloud_amount_amount_3',
				'period' => 30,
				'value' => '3',
				'metric_id' => null,
				'normilized_value' => '3',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'cloud_amount_height_3',
				'period' => 30,
				'value' => '04350',
				'metric_id' => null,
				'normilized_value' => '04350',
				'is_m' => 0,
			),
			
			array(
				'feature_code' => 'cloud_amount_amount_4',
				'period' => 30,
				'value' => '',
				'metric_id' => null,
				'normilized_value' => '',
				'is_m' => 1,
			),
			array(
				'feature_code' => 'cloud_amount_height_4',
				'period' => 30,
				'value' => '',
				'metric_id' => null,
				'normilized_value' => '',
				'is_m' => 1,
			),
			
			array(
				'feature_code' => 'cloud_amount_amount_total',
				'period' => 30,
				'value' => '7',
				'metric_id' => null,
				'normilized_value' => '7',
				'is_m' => 0,
			),
			
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
}

?>
