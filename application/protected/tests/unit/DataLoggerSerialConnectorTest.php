<?php

/**
 * Description of DataLoggerSerialConnectorTest
 *
 * 
 */
class DataLoggerSerialConnectorTest extends CTestCase
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
		
		$connector = new DataLoggerSerialConnector(LoggerFactory::getTestLogger(), $serial);
		
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
		
		$connector = new DataLoggerSerialConnector($logger, $serial);
		
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
//		$connector = new DataLoggerSerialConnector($logger, $serial);
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
//		$this->assertEquals(2, count($errors));
//		$this->assertEquals('stream_set_timeout() expects parameter 1 to be resource, null given', $errors[0]);
//		$this->assertEquals('Timeout exceeded during waiting message', $errors[0]);
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
					
					'readString' => array(
						'exception' => new InvalidArgumentException('Error on read'),
					),
				),
			
				'attributes' => array(
					'_dHandle' => STDOUT,
				),
			)
		);
		
		$connector = new DataLoggerSerialConnector($logger, $serial);
		
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
	
//	public function testConnect_NullResponse()
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
//			
//				'attributes' => array(
//					'_dHandle' => STDOUT,
//				),
//			)
//		);
//		
//		$connector = new DataLoggerSerialConnector($logger, $serial);
//		
//		$connector->setParams(array(
//				'port' => 'COM#',
//			)
//		);
//		
//		$output = null;
//		$result = $connector->readData($output);
//		
//		$this->assertTrue($result);
//		$this->assertNull($output);
//		
//		$errors = $connector->errors();
//		
//		$this->assertEquals(0, count($errors));
//	}
	
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
					
					'readString' => array(
						'return' => false,
					),
				),
			
				'attributes' => array(
					'_dHandle' => STDOUT,
				),
			)
		);
		
		$connector = new DataLoggerSerialConnector($logger, $serial);
		
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
		$this->assertEquals('Error during read message', $errors[0]);
	}
	
//	public function testConnect_ErrorOnRead1()
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
//					
//					'readString' => array(
//						'return' => "@DAWS01000101001900WS13000000000000RN1001007000009500BV1120SR1001000000000000SD10010000000TP10060HU1000TP20431TP30391WD13001001001PR116150693D2856$\r\n",
//					),
//				),
//			
//				'attributes' => array(
//					'_dHandle' => STDOUT,
//				),
//			)
//		);
//		
//		$connector = new DataLoggerSerialConnector($logger, $serial);
//		
//		$connector->setParams(array(
//				'port' => 'COM#',
//			)
//		);
//		
//		$output = null;
//		$result = $connector->readData($output);
//		
//		$expected = array('@DAWS01000101001900WS13000000000000RN1001007000009500BV1120SR1001000000000000SD10010000000TP10060HU1000TP20431TP30391WD13001001001PR116150693D2856$');
//		
//		$this->assertTrue($result);
//		$this->assertEquals($expected, $output);
//		
//		$errors = $connector->errors();
//		
//		$this->assertEquals(0, count($errors));
//	}
}

?>
