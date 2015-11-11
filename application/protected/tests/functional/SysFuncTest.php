<?php

/**
 * Description of SysFuncTest
 *
 * 
 */
class SysFuncTest extends CTestCase
{
	public function test_FakeSerialPortList()
	{
		Yii::app()->params['show_fake_com_ports'] = true;
		
		$result = SysFunc::getAvailableComPortsList();
		
		$expected = array(
			'COM1' => 'COM1',
			'COM2' => 'COM2',
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_SerialPortList_Linux()
	{
		$this->assertTrue(It::isLinux());
		
		Yii::app()->params['show_fake_com_ports'] = false;
		
		$result = SysFunc::getAvailableComPortsList();
		
		$expected = array(
			'COM1' => 'COM1 (/dev/ttyS0)'
		);
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_SerialPortList_Windows()
	{
		$this->assertTrue(It::isWindows());
		
		Yii::app()->params['show_fake_com_ports'] = false;
		
		$result = SysFunc::getAvailableComPortsList();
		
		$expected = array(
			'COM1' => 'Последовательный порт',
			'COM2' => 'Последовательный порт',
		);
		
		$this->assertEquals($expected, $result);
	}
}

?>
