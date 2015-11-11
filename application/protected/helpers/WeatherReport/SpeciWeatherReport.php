<?php

/**
 * Description of SpeciWeatherReport
 *
 * @author
 */
class SpeciWeatherReport extends MetarWeatherReport
{
	protected function getReportType() 
	{
		return 'SPECI';
	}
}

?>
