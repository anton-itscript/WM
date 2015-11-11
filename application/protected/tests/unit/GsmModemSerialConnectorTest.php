<?php

/**
 * Description of GsmModemSerialConnectorTest
 *
 * 
 */
class GsmModemSerialConnectorTest extends CTestCase
{
	public function testConnect_EmptyPort()
	{
		$logger = LoggerFactory::getTestLogger();
		$logger->log(__METHOD__);
		
		$serial = new Stub(array(
				'functions' => array(
					'deviceSet' => array(
						'return' => false,
					),
				),
			)
		);
		
		$connector = new GsmModemSerialConnector($logger, $serial);
		
		$output = null;
		$result = $connector->readData($output);
		
		$this->assertFalse($result);
		$this->assertNull($output);
		
		$errors = $connector->errors();
		
		$this->assertEquals(1, count($errors));
		$this->assertEquals('Can\'t set port "".', $errors[0]);
	}
	
	public function testConnect_CantOpenPort()
	{
		$logger = LoggerFactory::getTestLogger();
		$logger->log(__METHOD__);
		
		$serial = new Stub(array(
				'functions' => array(
					'deviceSet' => array(
						'return' => true,
					),
					
					'deviceOpen' => array(
						'return' => false,
					),
				),
			)
		);
		
		$connector = new GsmModemSerialConnector($logger, $serial);
		
		$connector->setParams(array(
				'port' => 'COM#',
			)
		);
		
		$output = null;
		$result = $connector->readData($output);
		
		$this->assertFalse($result);
		$this->assertNull($output);
		
		$errors = $connector->errors();
		
		$this->assertEquals(1, count($errors));
		$this->assertEquals('Can\'t open port "COM#".', $errors[0]);
	}
	
//	public function testConnect_WrongHandle()
//	{
//		$logger = LoggerFactory::getTestLogger();
//		$logger->log(__METHOD__);
//		
//		$serial = new Stub(array(
//				'functions' => array(
//					'deviceSet' => array(
//						'return' => true,
//					),
//					
//					'deviceOpen' => array(
//						'return' => true,
//					),
//				),
//			)
//		);
//		
//		$connector = new GsmModemSerialConnector($logger, $serial);
//		
//		$connector->setParams(array(
//				'port' => 'COM#',
//			)
//		);
//		
//		$output = null;
//		$result = $connector->readData($output);
//		
//		$this->assertFalse($result);
//		$this->assertNull($output);
//		
//		$errors = $connector->errors();
//		
//		$this->assertEquals(1, count($errors));
//		$this->assertEquals('stream_set_timeout() expects parameter 1 to be resource, null given', $errors[0]);
//	}
	
	public function testConnect_ExceptionOnRead()
	{
		$logger = LoggerFactory::getTestLogger();
		$logger->log(__METHOD__);
		
		$serial = new Stub(array(
				'functions' => array(
					'deviceSet' => array(
						'return' => true,
					),
					
					'deviceOpen' => array(
						'return' => true,
					),
					
					'readPort' => array(
						'exception' => new InvalidArgumentException('Error on read'),
					),
				),
			
				'attributes' => array(
					'_dHandle' => STDOUT,
				),
			)
		);
		
		$connector = new GsmModemSerialConnector($logger, $serial);
		
		$connector->setParams(array(
				'port' => 'COM#',
			)
		);
		
		$output = null;
		$result = $connector->readData($output);
		
		$this->assertFalse($result);
		$this->assertNull($output);
		
		$errors = $connector->errors();
		
		$this->assertEquals(1, count($errors));
		$this->assertEquals('Error on read', $errors[0]);
	}
	
	public function testConnect_NullResponse()
	{
		$logger = LoggerFactory::getTestLogger();
		$logger->log(__METHOD__);
		
		$serial = new Stub(array(
				'functions' => array(
					'deviceSet' => array(
						'return' => true,
					),
					
					'deviceOpen' => array(
						'return' => true,
					),
				),
			
				'attributes' => array(
					'_dHandle' => STDOUT,
				),
			)
		);
		
		$connector = new GsmModemSerialConnector($logger, $serial);
		
		$connector->setParams(array(
				'port' => 'COM#',
			)
		);
		
		$output = null;
		$result = $connector->readData($output);
		
		$this->assertTrue($result);
		$this->assertNull($output);
		
		$errors = $connector->errors();
		
		$this->assertEquals(0, count($errors));
	}
	
	public function testConnect_ErrorOnRead()
	{
		$logger = LoggerFactory::getTestLogger();
		$logger->log(__METHOD__);
		
		$serial = new Stub(array(
				'functions' => array(
					'deviceSet' => array(
						'return' => true,
					),
					
					'deviceOpen' => array(
						'return' => true,
					),
					
					'readPort' => array(
						'return' => false,
					),
				),
			
				'attributes' => array(
					'_dHandle' => STDOUT,
				),
			)
		);
		
		$connector = new GsmModemSerialConnector($logger, $serial);
		
		$connector->setParams(array(
				'port' => 'COM#',
			)
		);
		
		$output = null;
		$result = $connector->readData($output);
		
		$this->assertFalse($result);
		$this->assertNull($output);
		
		$errors = $connector->errors();
		
		$this->assertEquals(2, count($errors));
		$this->assertEquals('Error during send command: AT+CMGL="REC UNREAD"'."\r\n", $errors[0]);
		$this->assertEquals('Error during send command: AT+CMGD=0,1'."\r\n", $errors[1]);
	}
	
	public function testConnect_ErrorOnRead_NoErrors()
	{
		$logger = LoggerFactory::getTestLogger();
		$logger->log(__METHOD__);
		
		$serial = new Stub(array(
				'functions' => array(
					'deviceSet' => array(
						'return' => true,
					),
					
					'deviceOpen' => array(
						'return' => true,
					),
					
					'readPort' => array(
						'return' => "String1\r\nString2\r\nString3\r\n",
					),
				),
			
				'attributes' => array(
					'_dHandle' => STDOUT,
				),
			)
		);
		
		$connector = new GsmModemSerialConnector($logger, $serial);
		
		$connector->setParams(array(
				'port' => 'COM#',
			)
		);
		
		$output = null;
		$result = $connector->readData($output);
		
		$expected = array('String1', 'String2', 'String3');
		
		$this->assertTrue($result);
		$this->assertEquals($expected, $output);
		
		$errors = $connector->errors();
		
		$this->assertEquals(0, count($errors));
	}
}

?>
