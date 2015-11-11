<?php

/**
 * Description of RealDataLoggerSerialConnectorTest
 *
 * 
 */
class RealDataLoggerSerialConnectorTest extends CTestCase
{
	public function testConnect_NoErrors()
	{
        $logger = LoggerFactory::getFileLogger('tests/LoggerSerialConnectorTest');
		$serial = new PhpSerial($logger);
				
		$connector = new DataLoggerSerialConnector(new StubLogger(), $serial);
		
		$connector->setParams(array(
				'port' => 'COM1',
				'timeout' => 60,
				'flowControl' => 'xon/xoff',
				'baudrate' => 9600,
				'parity' => 'none',
				'stopBits' => 1,
				'dataBits' => 8,
			)
		);
		
		$output = null;
		$result = $connector->readData($output);
		
		$this->assertTrue($result);
		$this->assertTrue(count($output) > 0);
		$this->assertStringMatchesFormat('@DAWS%s$', $output[0]);
		
		$errors = $connector->errors();
		
		$this->assertEquals(0, count($errors));
	}
}

?>
