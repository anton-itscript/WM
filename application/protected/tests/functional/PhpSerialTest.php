<?php

/**
 * Description of PhpSerialTest
 *
 * 
 */
class PhpSerialTest extends CTestCase
{
	public function test_AtCommand()
	{
        $logger = LoggerFactory::getFileLogger('tests/PhpSerialTest');

		$connector = new PhpSerial($logger);
		
		$connector->deviceSet('COM1');
		
		$connector->confBaudRate(9600);
		$connector->confParity('none');
		$connector->confFlowControl('rts/cts');
		$connector->confCharacterLength(8);
		$connector->confStopBits(1);
		
		$connector->deviceOpen('r+b');		
		stream_set_timeout($connector->_dHandle, 2);
		
		$connector->sendMessage("AT\r\n");		
		$result = $connector->readPort();
		
		$connector->deviceClose();
		
		$result = explode("\r\n", $result);
		
		$this->assertEquals(4, count($result));
		
		$this->assertEquals('AT', $result[0]);
		$this->assertEmpty($result[1]);
		$this->assertEquals('OK', $result[2]);
		$this->assertEmpty($result[3]);
	}
	
	public function test_AtAndCpinCommands()
	{
		$connector = new PhpSerial();
		
		$connector->deviceSet('COM1');
		
		$connector->confBaudRate(9600);
		$connector->confParity('none');
		$connector->confFlowControl('rts/cts');
		$connector->confCharacterLength(8);
		$connector->confStopBits(1);
		
		$connector->deviceOpen('r+b');		
		stream_set_timeout($connector->_dHandle, 2);
		
		$connector->sendMessage("AT+CPIN?\r\n");		
		$result = $connector->readPort();
		
		$connector->deviceClose();
		
		$result = explode("\r\n", $result);
		
		$this->assertEquals(4, count($result));
		
		$this->assertEquals('AT+CPIN?', $result[0]);
		$this->assertEmpty($result[1]);
		$this->assertEquals('+CPIN: READY', $result[2]);
		$this->assertEmpty($result[3]);
	}
}

?>
