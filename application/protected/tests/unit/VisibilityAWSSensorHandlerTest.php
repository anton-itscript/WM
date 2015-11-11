<?php

/**
 * Description of VisibilityAWSSensorHandlerTest
 *
 * @author
 */
class VisibilityAWSSensorHandlerTest extends CTestCase
{
	public function setUp() 
	{
		error_reporting(E_ALL ^ E_NOTICE);
	} 
	
	public function test_PrepareDataPairs_WrongSize()
	{
		$handler = new VisibilityAWSSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '1';
		
		$this->assertFalse($handler->_prepareDataPairs());
	}
	
	public function test_PrepareDataPairs()
	{
		$handler = new VisibilityAWSSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '1610600000MMMMMMMMM';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'visibility_1',
				'period' => 1,
				'value' => '16106',
				'metric_id' => null,
				'normilized_value' => '16106',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'visibility_10',
				'period' => 10,
				'value' => '00000',
				'metric_id' => null,
				'normilized_value' => '00000',
				'is_m' => 0,
			),
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
	
	public function test_PrepareDataPair_Empty1minAverage()
	{
		$handler = new VisibilityAWSSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = 'MMMMM00020MMMMMMMMM';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'visibility_1',
				'period' => 1,
				'value' => 'MMMMM',
				'metric_id' => null,
				'normilized_value' => 'MMMMM',
				'is_m' => 1,
			),
			array(
				'feature_code' => 'visibility_10',
				'period' => 10,
				'value' => '00020',
				'metric_id' => null,
				'normilized_value' => '00020',
				'is_m' => 0,
			),
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
	
	public function test_PrepareDataPair_Empty10minAverage()
	{
		$handler = new VisibilityAWSSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '16106MMMMMMMMMMMMMM';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'visibility_1',
				'period' => 1,
				'value' => '16106',
				'metric_id' => null,
				'normilized_value' => '16106',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'visibility_10',
				'period' => 10,
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
