<?php

/**
 * Description of ProcessPidTest
 *
 * 
 */
class ProcessPidTest extends CTestCase 
{
	public function test_IsActiveProcess()
	{
		$pid = getmypid();
		
		$this->assertTrue(ProcessPid::isActiveProcess($pid));
	}
}

?>
