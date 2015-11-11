<?php

/*
 * Is called using command: "php console.php getXml", doesn't requires arguements
 * Is called every minute using schtasks
 * 
 * Reads FTP folder, grabs XML-messages from AWOS station and converts them into regular message
 * 
 */

class GetXmlCommand extends CConsoleCommand
{
    public function run($args) 
    {
		// this block is not related to XMLs, but it empties heavy data "explanations" from old records
        $cur_hour = date('H');
        $cur_min  = date('i');    
        
		if (!in_array($cur_min, array(1,6,11,16,21,26,31,36,41,46,51,56)))
		{
            if (in_array($cur_min, array(10, 40)))
			{
                $criteria = new CDbCriteria();
                $criteria->condition = "created <= '".date('Y-m-d H:i:s', mktime() - 86400)."' AND serialized_report_explanations <> ''";
                
				$processedReports = ScheduleReportProcessed::model()->findAll($criteria);
                
				foreach ($processedReports as $processedReport)
				{
					$processedReport->serialized_report_explanations = '';
					$processedReport->save();
				}
            }
			
            exit;
        }
        
		// check if it is time to read FTP folder
        $settings = Settings::model()->findByPk(1);
        
        $check = false;
        
        switch ($settings->xml_check_frequency)
		{
            case 60: 
                if ($cur_min == 1) $check = true;
                
				break;
            case 30:
                if (in_array($cur_min, array(1, 31))) $check = true;
                
				break;
            case 15:
                if (in_array($cur_min, array(1, 16, 31, 46))) $check = true;
                
				break;
            case 5:
                if (in_array($cur_min, array(1, 6, 11, 16, 21, 26, 31, 36, 41, 46, 51, 56))) $check = true;                
                
				break;
        }
        
        if ($check === false)
		{
            exit;
		}
		
		// XmlLog - is a log of XML reading and parsing
        $log = new XmlLog();
		
        try
		{
			$log->comment = $this->process($settings);
        }
		catch (Exception $exc)
		{
			$log->comment = $exc->getMessage();
        }

        $log->save();
    }

	
	// read all files from FTP folder, copy them to project's folder and run converting
    public function process($settings)
	{
        if (!is_dir($settings->xml_messages_path)) 
		{
			throw new Exception('Folder doesn\'t exists: '.$settings->xml_messages_path);
		}
		
        $xml_files = array();
        $dir = dirname(Yii::app()->request->scriptFile) . DIRECTORY_SEPARATOR .'files'. DIRECTORY_SEPARATOR .'xmls';
        
		if ($handle = opendir($settings->xml_messages_path))
		{
			if (!file_exists($dir))
			{
                if (@mkdir($dir, 0777, true) === false)
				{
                    throw new Exception('Failed to create '. $dir);                     
                }
            }            
            
            while (false !== ($entry = readdir($handle)))
			{
				if (strstr(strtolower($entry), '.xml'))
				{
                    if (copy($settings->xml_messages_path . DIRECTORY_SEPARATOR . $entry, $dir . DIRECTORY_SEPARATOR . $entry))
					{
                        @unlink($settings->xml_messages_path . DIRECTORY_SEPARATOR . $entry);
                        $xml_files[] = $entry;
                    }
                }
            }
			
            closedir($handle);
        }
        
        if (!$xml_files)
		{
            throw new Exception('No XML files in '. $dir);  
        }
        
        $result = array();
        $total = count($xml_files);
        
		$result[] = $total ." XML file(s) were found";
        
		foreach ($xml_files as $xmlFile)
		{
            try
			{
				// convert XML message into regular message
                $obj = new ConvertXmlToMessages();
                
				$result[] = $obj->process($dir . DIRECTORY_SEPARATOR . $xmlFile); 
                $result[] = $xmlFile ." : processed";
            }
			catch (Exception $exc)
			{
				$result[] = $exc->getMessage();
            }
        }   
		
        return implode("\n", $result);
    }
}
?>