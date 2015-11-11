<?php

/**
 * Description of ListenerLogTest
 *
 * @author
 */
class ListenerLogTest extends CTestCase
{
	public function testGetMessageWithTime_OneCall()
	{
		$log = new ListenerLog();
		
		StubFactory::clear();
		StubFactory::stubFunction('ListenerLog->find', array('return' => $log));
		
		$message = ListenerLog::getMessageWithTime(1, '123');
		
		$this->assertSame($log, $message);
		$this->assertEquals(1, StubFactory::getStubCallCount('ListenerLog->find'));
	}
	
	public function testGetMessageWithTime_TwoDifferentCalls()
	{
		$log = new ListenerLog();
		
		StubFactory::clear();
		StubFactory::stubFunction('ListenerLog->find', array('return' => $log));
		
		$message1 = ListenerLog::getMessageWithTime(1, '1234');
		$message2 = ListenerLog::getMessageWithTime(1, '1234', '123');
		
		$this->assertSame($log, $message1);
		$this->assertSame($log, $message2);
		
		$this->assertEquals(2, StubFactory::getStubCallCount('ListenerLog->find'));
	}
	
	public function testGetMessageWithTime_TwoSameCalls()
	{
		$log = new ListenerLog();
		
		StubFactory::clear();
		StubFactory::stubFunction('ListenerLog->find', array('return' => $log));
		
		$message1 = ListenerLog::getMessageWithTime(1, '12345', '423');
		$message2 = ListenerLog::getMessageWithTime(1, '12345', '423');
		
		$this->assertSame($log, $message1);
		$this->assertSame($log, $message2);
		
		$this->assertEquals(1, StubFactory::getStubCallCount('ListenerLog->find'));
	}
}

?>
