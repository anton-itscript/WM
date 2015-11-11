<?php

/**
 * Description of RealGsmModemSerialConnectorTest
 *
 * 
 */
class RealGsmModemSerialConnectorTest extends CTestCase
{
	public function testConnect_NoErrors()
	{
        $logger = LoggerFactory::getFileLogger('tests/RealGsmModemSerialConnectorTest');
        $serial = new PhpSerial($logger);

				
		$connector = new GsmModemSerialConnector(new StubLogger(), $serial);
		
		$connector->setParams(array(
				'port' => 'COM1',
				'timeout' => 5,
				'flowControl' => 'rts/cts',
				'baudrate' => 9600,
				'parity' => 'none',
				'stopBits' => 1,
				'dataBits' => 8,
			)
		);
		
		$output = null;
		$result = $connector->readData($output);
		
		$this->assertTrue($result);
		
		$errors = $connector->errors();
		$this->assertEquals(0, count($errors));
		
		$this->assertEquals(2, count($output));
		$this->assertEquals('AT+CMGL="ALL"', $output[0]);
		$this->assertEquals('OK', $output[1]);
	}
}

?>
