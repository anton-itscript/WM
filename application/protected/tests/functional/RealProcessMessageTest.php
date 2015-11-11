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
class RealProcessMessageTest extends CTestCase
{
	protected $_messages = array();
	
	protected function createBlankMessage()
	{
		$message = new ListenerLog();
		
		$this->_messages[] = $message;
		
		return $message;
	}
	
	public function setUp() 
	{
		Yii::app()->params['isUnitTests'] = false;
	} 
	
	public function tearDown() 
	{
		foreach ($this->_messages as $message)
		{
			$message->delete();
		}
		
		Yii::app()->params['isUnitTests'] = true;
	} 

	public function test_Run_NullMessage()
	{
		$processMessage = new ProcessMessage(LoggerFactory::getTestLogger(), null);
		$processMessage->run();
		
		$this->assertEquals(0, count($processMessage->warnings));
		$this->assertEquals(1, count($processMessage->errors));
		
		$this->assertEquals(array('null_message', 'Message object is null'), $processMessage->errors[0]);
	}
	
	public function test_Run_EmptyMessage()
	{
		$listenerLog = $this->createBlankMessage();
		$listenerLog->station_id = 1;
		
		$listenerLog->save();
		
		$processMessage = new ProcessMessage(LoggerFactory::getTestLogger(), $listenerLog);
		$processMessage->run();
		
		$this->assertEquals(0, count($processMessage->warnings));
		$this->assertEquals(2, count($processMessage->errors));
		
		$this->assertEquals(array('start_missed', 'Record does not start with @'), $processMessage->errors[0]);
		$this->assertEquals(array('end_missed', 'Record does not end with $'), $processMessage->errors[1]);
	}
	
	public function test_Run_WrongCrc()
	{
		$listenerLog = $this->createBlankMessage();
		$listenerLog->message = '@123$';
		$listenerLog->station_id = 1;
		
		$listenerLog->save();
		
		$processMessage = new ProcessMessage(LoggerFactory::getTestLogger(), $listenerLog);
		$processMessage->run();
		
		$this->assertEquals(0, count($processMessage->warnings));
		$this->assertEquals(1, count($processMessage->errors));
		
		$this->assertEquals(array('crc_wrong', 'CRC code is incorrect'), $processMessage->errors[0]);
	}
	
	public function test_Run_UnknownStation()
	{
		$listenerLog = $this->createBlankMessage();
		$listenerLog->message = '@DAWS02000101001900WS13000000000000RN1001007000009500BV1120SR1001000000000000SD10010000000TP10060HU1000TP20431TP30391WD13001001001PR116150EE92A666$';
		$listenerLog->station_id = 1;
		
		$listenerLog->save();
		
		$processMessage = new ProcessMessage(LoggerFactory::getTestLogger(), $listenerLog);
		$processMessage->run();
		
		$this->assertEquals(0, count($processMessage->warnings));
		$this->assertEquals(1, count($processMessage->errors));
		
		$this->assertEquals(array('unknown_station', 'Can not find station for station_id_code="AWS02" in the DB, station_id="0"'), $processMessage->errors[0]);
	}
	
	public function test_Run_AWS1Station_NoErrors()
	{
		$listenerLog = $this->createBlankMessage();
		$listenerLog->message = '@DAWS01000101001900WS13000000000000RN1001007000009500BV1120SR1001000000000000SD10010000000TP10060HU1000TP20431TP30391WD13001001001PR116150693D2856$';
		$listenerLog->station_id = 1;
		
		$listenerLog->save();
		
		$processMessage = new ProcessMessage(LoggerFactory::getTestLogger(), $listenerLog);
		$processMessage->run();
		
		$this->assertEquals(0, count($processMessage->warnings));
		$this->assertEquals(0, count($processMessage->errors));
	}
}
?>