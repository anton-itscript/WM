<?php

/**
 * Wrapper to open protected functions 
 */
class ProcessMessageWrapper extends ProcessMessage
{
	public function parseSensorsValuesPublic($input)
	{
		return parent::parseSensorsValues($input);
	}
	
	public function parseSensorsValuesOldPublic($input)
	{
		return parent::parseSensorsValuesOld($input);
	}
}

/**
 * Description of ProcessMessageTest
 *
 * @author
 */
class ProcessMessageTest extends CTestCase
{
	public function setUp() 
	{
		error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);
	} 
	
	public function test_Run_NullMessage()
	{
		$settings = new Settings();
		
		$processMessage = new ProcessMessage(LoggerFactory::getTestLogger(), null);
		
		StubFactory::clear();
		StubFactory::stubFunction('Settings->findByPk', array('return' => $settings));
		
		$processMessage->run();
		
		$this->assertEquals(0, count($processMessage->warnings));
		$this->assertEquals(1, count($processMessage->errors));
		
		$this->assertEquals(array('null_message', 'Message object is null'), $processMessage->errors[0]);
		
		// Call of stub in destructor
		unset($processMessage);
		
		$this->assertEquals(1, StubFactory::getStubCallCount('Settings->findByPk'));
	}
	
	public function test_Run_EmptyMessage()
	{
		$settings = new Settings();
		
		$listenerLog = new ListenerLog();
		
		$processMessage = new ProcessMessage(LoggerFactory::getTestLogger(), $listenerLog);
		
		StubFactory::clear();
		StubFactory::stubFunction('ListenerLogProcessError->save');
		StubFactory::stubFunction('ListenerLog->save');
		StubFactory::stubFunction('Settings->findByPk', array('return' => $settings));
		
		$processMessage->run();
		
		$this->assertEquals(0, count($processMessage->warnings));
		$this->assertEquals(2, count($processMessage->errors));
		
		$this->assertEquals(array('start_missed', 'Record does not start with @'), $processMessage->errors[0]);
		$this->assertEquals(array('end_missed', 'Record does not end with $'), $processMessage->errors[1]);
		
		// Call of stub in destructor
		unset($processMessage);
		
		$this->assertEquals(0, StubFactory::getStubCallCount('Settings->findByPk'));
	}
	
	public function test_Run_WrongCrc()
	{
		$settings = new Settings();
		
		$listenerLog = new ListenerLog();
		$listenerLog->message = '@123$';
		
		$processMessage = new ProcessMessage(LoggerFactory::getTestLogger(), $listenerLog);
		
		StubFactory::clear();
		StubFactory::stubFunction('ListenerLogProcessError->save');
		StubFactory::stubFunction('ListenerLog->save');
		StubFactory::stubFunction('Settings->findByPk', array('return' => $settings));
		
		$processMessage->run();
		
		$this->assertEquals(0, count($processMessage->warnings));
		$this->assertEquals(1, count($processMessage->errors));
		
		$this->assertEquals(array('crc_wrong', 'CRC code is incorrect'), $processMessage->errors[0]);
				
		// Call of stub in destructor
		unset($processMessage);
		
		$this->assertEquals(0, StubFactory::getStubCallCount('Settings->findByPk'));
	}
	
	public function test_Run_UnknownStation()
	{
		$settings = new Settings();
		
		$listenerLog = new ListenerLog();
		$listenerLog->message = '@DAWS01000101001900WS13000000000000RN1001007000009500BV1120SR1001000000000000SD10010000000TP10060HU1000TP20431TP30391WD13001001001PR116150693D2856$';
		
		$processMessage = new ProcessMessage(LoggerFactory::getTestLogger(), $listenerLog);
		
		StubFactory::clear();
		StubFactory::stubFunction('ListenerLogProcessError->save');
		StubFactory::stubFunction('ListenerLog->save');
		StubFactory::stubFunction('Station->find');
		StubFactory::stubFunction('Settings->findByPk', array('return' => $settings));
		
		$processMessage->run();
		
		$this->assertEquals(0, count($processMessage->warnings));
		$this->assertEquals(1, count($processMessage->errors));
		
		$this->assertEquals(array('unknown_station', 'Can not find station for station_id_code="AWS01" in the DB, station_id="0"'), $processMessage->errors[0]);
				
		// Call of stub in destructor
		unset($processMessage);
		
		$this->assertEquals(0, StubFactory::getStubCallCount('Settings->findByPk'));
		$this->assertEquals(1, StubFactory::getStubCallCount('Station->find'));
	}
	
	public function test_Run_AWS01Station()
	{
		$settings = new Settings();
		
		$listenerLog = new ListenerLog();
		$listenerLog->message = '@DAWS01000101001900WS13000000000000RN1001007000009500BV1120SR1001000000000000SD10010000000TP10060HU1000TP20431TP30391WD13001001001PR116150693D2856$';
		
		$processMessage = new ProcessMessage(LoggerFactory::getTestLogger(), $listenerLog);
		
		$station = new Station();
		
		$station->station_id = 1;
		$station->station_id_code = 'AWS01';
		$station->station_type = 'aws';
		
		$handlers = array();
		
		$sensorHandler = new SensorDBHandler();
		$sensorHandler->handler_id_code = 'WindSpeed';
		
		$sensor = new StationSensor();
		$sensor->handler = $sensorHandler;
		$sensor->station_sensor_id = 1;
		
		StubFactory::clear();
		StubFactory::stubFunction('ListenerLogProcessError->save');
		StubFactory::stubFunction('ListenerLog->save');
		StubFactory::stubFunction('Station->find', array('return' => $station));
		StubFactory::stubFunction('Settings->findByPk', array('return' => $settings));
		StubFactory::stubFunction('SensorData->find');
		StubFactory::stubFunction('SensorData->save');
		StubFactory::stubFunction('StationCalculation->findAll', array('return' => $handlers));
		StubFactory::stubFunction('StationSensor->find', array('return' => $sensor));
		
		$processMessage->run();
		
		$this->assertEquals(9, count($processMessage->warnings));
		$this->assertEquals(0, count($processMessage->errors));
		
		// Call of stub in destructor
		unset($processMessage);
		
		$this->assertEquals(0, StubFactory::getStubCallCount('Settings->findByPk'));
		$this->assertEquals(1, StubFactory::getStubCallCount('Station->find'));
		$this->assertEquals(2, StubFactory::getStubCallCount('ListenerLog->save'));
		$this->assertEquals(4, StubFactory::getStubCallCount('SensorData->save'));
	}
	
	public function test_ParseSensorPairs_AWSMessage()
	{
		$message = '@DAWS01000101001900WS13000000000000RN1001007000009500BV1120SR1001000000000000SD10010000000TP10060HU1000TP20431TP30391WD13001001001PR116150693D2856$';
		
		$message = substr($message, 19, -9);
		
		$processMessage = new ProcessMessageWrapper(LoggerFactory::getTestLogger(), null);
		
		$result = $processMessage->parseSensorsValuesPublic($message);
		
		$expected = array(
			array('WS1', '3000000000000'),
			array('RN1', '001007000009500'),
			array('BV1', '120'),
			array('SR1', '001000000000000'),
			array('SD1', '0010000000'),
			array('TP1', '0060'),
			array('HU1', '000'),
			array('TP2', '0431'),
			array('TP3', '0391'),
			array('WD1', '3001001001'),
			array('PR1', '16150'),
		);
		
		$this->assertEquals(count($expected), count($result));
		$this->assertEquals($expected, $result);
	}
	
	public function test_ParseSensorPairs_RgMessage_Long()
	{
		$message = '@DDCP1130516165512200RN1R60110000000020000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000E3CABA3E$';
		
		$message = substr($message, 21, -9);
		
		$processMessage = new ProcessMessageWrapper(LoggerFactory::getTestLogger(), null);
		
		$result = $processMessage->parseSensorsValuesPublic($message);
		
		$expected = array(
			array('RN1', 'R60110000000020000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000'),
		);
		
		$this->assertEquals(count($expected), count($result));
		$this->assertEquals($expected, $result);
	}
	
	public function test_ParseSensorPairs_RgMessage_Short()
	{
		$message = '@DDCP1130516170012200RN1A0100316C1EECEB$';
		
		$message = substr($message, 21, -9);
		
		$processMessage = new ProcessMessageWrapper(LoggerFactory::getTestLogger(), null);
		
		$result = $processMessage->parseSensorsValuesPublic($message);
		
		$expected = array(
			array('RN1', 'A010031'),
		);
		
		$this->assertEquals(count($expected), count($result));
		$this->assertEquals($expected, $result);
	}
	
	public function test_ParseSensorPairs_RgMessage_WithUnknownValues()
	{
		$message = '@DSYD01111105200800WS1301350200MMMMRN1001000000000000BV1120SR1001057001057003SD10010000000TP10050HU1000TP20301TP30106WD13360360360PR117500SL1MMMMMMMMMMMM4683A14B$';
		
		$message = substr($message, 19, -9);
		
		$processMessage = new ProcessMessageWrapper(LoggerFactory::getTestLogger(), null);
		
		$result = $processMessage->parseSensorsValuesPublic($message);
		
		$expected = array(
			array('WS1', '301350200MMMM'),
			array('RN1', '001000000000000'),
			array('BV1', '120'),
			array('SR1', '001057001057003'),
			array('SD1', '0010000000'),
			array('TP1', '0050'),
			array('HU1', '000'),
			array('TP2', '0301'),
			array('TP3', '0106'),
			array('WD1', '3360360360'),
			array('PR1', '17500'),
			array('SL1', 'MMMMMMMMMMMM'),
		);
		
		$this->assertEquals(count($expected), count($result));
		$this->assertEquals($expected, $result);
	}
	
	public function test_ParseSensorPairs_RgMessage_WithUnknownValuesOld()
	{
		$message = '@DSYD01111105200800WS1301350200MMMMRN1001000000000000BV1120SR1001057001057003SD10010000000TP10050HU1000TP20301TP30106WD13360360360PR117500SL1MMMMMMMMMMMM4683A14B$';
		
		$message = substr($message, 19, -9);
		
		$processMessage = new ProcessMessageWrapper(LoggerFactory::getTestLogger(), null);
		
		$result = $processMessage->parseSensorsValuesOldPublic($message);
		
		$expected = array(
			array('WS1', '301350200MMMM'),
			array('RN1', '001000000000000'),
			array('BV1', '120'),
			array('SR1', '001057001057003'),
			array('SD1', '0010000000'),
			array('TP1', '0050'),
			array('HU1', '000'),
			array('TP2', '0301'),
			array('TP3', '0106'),
			array('WD1', '3360360360'),
			array('PR1', '17500'),
			array('SL1', 'MMMMMMMMMMMM'),
		);
		
		$this->assertEquals(count($expected), count($result));
		$this->assertEquals($expected, $result);
	}
}

?>
