<?php

class SimpleLogWriterStub implements ILogWriter
{
	public $counter = 0;
	
	public function write($message)
	{
		$this->counter += 1;
	}
}

class LogWriterStub extends SimpleLogWriterStub 
{
	public $messages = array();
	
	public function write($message)
	{
		parent::write($message);
		
		if (is_array($message))
		{
			foreach ($message as $value) 
			{
				$this->messages[] = $value;
			}
		}
		else
		{
			$this->messages[] = $message;
		}
	}
}

class LoggerTest extends CTestCase
{
	public function testLog_BufferIsOk()
	{
		$log_writer = new SimpleLogWriterStub();

		$logger = new Logger($log_writer, 3);
		
		$logger->log('123');
		
		$this->assertEquals(0, $log_writer->counter);
		
		$logger->log('436');
		
		$this->assertEquals(0, $log_writer->counter);
		
		$logger->log('4342');
		
		$this->assertEquals(1, $log_writer->counter);
	}
	
	public function test_Log_NoParamsForFormatting()
	{
		$log_writer = new LogWriterStub();

		$logger = new Logger($log_writer, 1);
		
		$logger->log('123', array('param_1' => '12345'));
		
		$this->assertEquals(1, count($log_writer->messages));		
		$this->assertStringMatchesFormat('%s: 123: param_1= 12345', $log_writer->messages[0]);
	}
	
	public function test_Log_HasParamsForShadowing_Object()
	{
		$log_writer = new LogWriterStub();

		$logger = new Logger($log_writer, 1);
		
		$logger->log('123', array('param_0' => 'test',  'param_1' => 'efewfwg', 'param_2' => new stdClass()));
		
		$this->assertEquals(1, count($log_writer->messages));
		$this->assertStringMatchesFormat('%s: 123: param_0= test; param_1= efewfwg; param_2= #Object(stdClass)', $log_writer->messages[0]);
	}
}

?>
