<?php

/**
 * Description of VisibilityAwsDlm13mSensorHandlerTest
 *
 * @author
 */
class VisibilityAwsDlm13mSensorHandlerTest extends CTestCase
{
	public function setUp() 
	{
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
	} 
	
	public function test_PrepareDataPairs_WrongSize()
	{
		$handler = new VisibilityAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '1';
		
		$this->assertFalse($handler->_prepareDataPairs());
	}
	
	public function test_PrepareDataPairs_EmptyStatus()
	{
		$handler = new VisibilityAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '1610600000M123.12';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'extinction',
				'period' => 1,
				'value' => '123.12',
				'metric_id' => null,
				'normilized_value' => '123.12',
				'is_m' => 0,
			),
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
			array(
				'feature_code' => 'status',
				'period' => 1,
				'value' => '',
				'metric_id' => null,
				'normilized_value' => '',
				'is_m' => 1,
			),
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
	
	public function test_PrepareDataPairs_1byteStatus()
	{
		$handler = new VisibilityAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '1610600000M123.12-E-';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'extinction',
				'period' => 1,
				'value' => '123.12',
				'metric_id' => null,
				'normilized_value' => '123.12',
				'is_m' => 0,
			),
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
			array(
				'feature_code' => 'status',
				'period' => 1,
				'value' => 'E',
				'metric_id' => null,
				'normilized_value' => 'E',
				'is_m' => 0,
			),
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
	
	public function test_PrepareDataPairs_2bytesStatus()
	{
		$handler = new VisibilityAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '1610600000M123.12-ER-';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'extinction',
				'period' => 1,
				'value' => '123.12',
				'metric_id' => null,
				'normilized_value' => '123.12',
				'is_m' => 0,
			),
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
			array(
				'feature_code' => 'status',
				'period' => 1,
				'value' => 'ER',
				'metric_id' => null,
				'normilized_value' => 'ER',
				'is_m' => 0,
			),
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
	
	public function test_PrepareDataPairs_3bytesStatus()
	{
		$handler = new VisibilityAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '1610600000M123.12-ERR-';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'extinction',
				'period' => 1,
				'value' => '123.12',
				'metric_id' => null,
				'normilized_value' => '123.12',
				'is_m' => 0,
			),
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
			array(
				'feature_code' => 'status',
				'period' => 1,
				'value' => 'ERR',
				'metric_id' => null,
				'normilized_value' => 'ERR',
				'is_m' => 0,
			),
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
	
	public function test_PrepareDataPairs_Empty1minAverage_TakeValueFromExtinctionCoeff()
	{
		$handler = new VisibilityAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = 'MMMMM16060M001.00-ERR-';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'extinction',
				'period' => 1,
				'value' => '001.00',
				'metric_id' => null,
				'normilized_value' => '001.00',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'visibility_1',
				'period' => 1,
				'value' => 3.0,
				'metric_id' => null,
				'normilized_value' => 3.0,
				'is_m' => 1,
			),
			array(
				'feature_code' => 'visibility_10',
				'period' => 10,
				'value' => '16060',
				'metric_id' => null,
				'normilized_value' => '16060',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'status',
				'period' => 1,
				'value' => 'ERR',
				'metric_id' => null,
				'normilized_value' => 'ERR',
				'is_m' => 0,
			),
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
}

?>
