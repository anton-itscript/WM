<?php

class ItTest extends CTestCase
{
	public function test_IsLinux()
	{
		$result = It::isLinux();
		
		$expected = (substr(strtolower(php_uname('s')), 0, 5) === 'linux');
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_IsWindows()
	{
		$result = It::isWindows();
		
		$expected = (substr(strtolower(php_uname('s')), 0, 7) === 'windows');
		
		$this->assertEquals($expected, $result);
	}
	
	
	public function test_String2Binary()
	{
		$s = '0000000100000011';
		
		$result = It::string2binary($s);
		
		$expected = chr(1) . chr(3);
		
		$this->assertEquals($expected, $result);
	}
	
	
	public function test_PrepareStringCSV()
	{
		$s = array(
			'1' => array(
				'1.1' => '213213',
				'1.2' => '21213',
				'1.3' => '2133',
			),
			
			'2' => array(
				'2.1' => '2113',
				'2.2' => '2132',
				'2.3' => '3213',
			),
		);
		
		$result = It::prepareStringCSV($s);
		
		$expected = '"1.1","1.2","1.3"'."\n".
					'"213213","21213","2133"'."\n".
					'"2113","2132","3213"'."\n";
		
		$this->assertEquals($expected, $result);
	}
	
	
	public function test_CreateTextPreview_ShortString()
	{
		$s = 'Test string';
		$limit = 20;
		
		$result = It::createTextPreview($s, $limit);
		
		$expected = 'Test string';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_CreateTextPreview_LongString()
	{
		$s = 'Test string';
		$limit = 10;
		
		$result = It::createTextPreview($s, $limit);
		
		$expected = 'Test strin';
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_CreateTextPreview_CustomReplacement()
	{
		$s = 'Test string';
		$limit = 10;
		$replacement = '>>>';
		
		$result = It::createTextPreview($s, $limit, $replacement);
		
		$expected = 'Test st>>>';
		
		$this->assertEquals($expected, $result);
	}
	
	
	public function test_IsGuest()
	{
		$result = It::isGuest();
		
		$this->assertTrue($result);
	}
	
	public function test_IsAdmin()
	{
		$result = It::isAdmin();
		
		$this->assertNull($result);
	}
	
	public function test_UserId()
	{
		$result = It::userId();
		
		$this->assertNull($result);
	}
	
	
	public function test_ConvertMetric_SameMetrics()
	{
		$result = It::convertMetric(12, 'celsius', 'celsius');
		
		$expected = 12;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_ConvertMetric_UnknownMetric()
	{
		$result = It::convertMetric(12, 'celsius', 'hz');
		
		$expected = -999999;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_ConvertMetric_Celsius2Fahrenheit()
	{
		$result = It::convertMetric(12, 'celsius', 'farenheit');
		
		$expected = 53.6;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_ConvertMetric_Celsius2Kelvin()
	{
		$result = It::convertMetric(12, 'celsius', 'kelvin');
		
		$expected = 285.15;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_ConvertMetric_Fahrenheit2Celsius()
	{
		$result = It::convertMetric(50, 'farenheit', 'celsius');
		
		$expected = 10;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_ConvertMetric_Fahrenheit2Kelvin()
	{
		$result = It::convertMetric(50, 'farenheit', 'kelvin');
		
		$expected = 283.15;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_ConvertMetric_Kelvin2Celsius()
	{
		$result = It::convertMetric(298.15, 'kelvin', 'celsius');
		
		$expected = 25;
		
		$this->assertEquals($expected, $result);
	}
	
	public function test_ConvertMetric_Kelvin2Fahrenheit()
	{
		$result = It::convertMetric(283.15, 'kelvin', 'farenheit');
		
		$expected = 50;
		
		$this->assertEquals($expected, $result);
	}
}

?>
