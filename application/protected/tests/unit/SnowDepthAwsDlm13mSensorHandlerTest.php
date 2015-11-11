<?php

/**
 * Description of SnowDepthAwsDlm13mSensorHandlerTest
 *
 * @author
 */
class SnowDepthAwsDlm13mSensorHandlerTest extends CTestCase
{
	public function setUp() 
	{
		error_reporting(E_ALL ^ E_NOTICE);
	} 
	
	public function test_PrepareDataPairs_WrongSize()
	{
		$handler = new SnowDepthAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '1';
		
		$result = $handler->_prepareDataPairs();
		
		$this->assertFalse($result);
	}
	
	public function test_PrepareDataPairs_NormalMessage()
	{
		$handler = new SnowDepthAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '12432.1234';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'snow_depth',
				'period' => 1,
				'value' => '432.1234',
				'metric_id' => null,
				'normilized_value' => '432.1234',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'error_code',
				'period' => 1,
				'value' => '12',
				'metric_id' => null,
				'normilized_value' => '12',
				'is_m' => 0,
			),
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
	
	public function test_PrepareDataPair_EmptyErrorCode()
	{
		$handler = new SnowDepthAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = 'MM123.1234';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'snow_depth',
				'period' => 1,
				'value' => '123.1234',
				'metric_id' => null,
				'normilized_value' => '123.1234',
				'is_m' => 0,
			),
			array(
				'feature_code' => 'error_code',
				'period' => 1,
				'value' => 'MM',
				'metric_id' => null,
				'normilized_value' => 'MM',
				'is_m' => 1,
			),
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
	
	public function test_PrepareDataPair_EmptySnowDepth()
	{
		$handler = new SnowDepthAwsDlm13mSensorHandler(LoggerFactory::getTestLogger());
		$handler->incoming_sensor_value = '14MMMMMMMM';
		$handler->sensor_features_info = array();
		
		$this->assertTrue($handler->_prepareDataPairs());
		
		$expected = array(
			array(
				'feature_code' => 'snow_depth',
				'period' => 1,
				'value' => 'MMMMMMMM',
				'metric_id' => null,
				'normilized_value' => 'MMMMMMMM',
				'is_m' => 1,
			),
			
			array(
				'feature_code' => 'error_code',
				'period' => 1,
				'value' => '14',
				'metric_id' => null,
				'normilized_value' => '14',
				'is_m' => 0,
			),
		);
		
		$this->assertEquals(count($expected), count($handler->prepared_pairs));
		$this->assertEquals($expected, $handler->prepared_pairs);
	}
}

?>

