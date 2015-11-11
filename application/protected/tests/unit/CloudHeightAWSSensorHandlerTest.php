<?php

/**
 * Description of CloudHeightAWSSensorHandlerTest
 *
 * @author
 */
class CloudHeightAWSSensorHandlerTest extends CTestCase
{
	public function setUp() 
	{
		error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);
	} 
	
	public function test_PrepareDataPairs_WrongSize()
	{
		$handler = new CloudHeightAWSSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '1';
		
		$this->assertFalse($handler->_prepareDataPairs());
	}
	
	public function test_PrepareDataPairs()
	{
		$handler = new CloudHeightAWSSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '66551234567890543210987632154879601000050000';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
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
		$handler = new CloudHeightAWSSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = 'MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM';
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
}

?>
