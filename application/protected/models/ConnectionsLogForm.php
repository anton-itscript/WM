<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ConnectionsLogForm extends CFormModel
{
    public $source;
    public $date_from;
    public $time_from;
    public $date_to;
    public $time_to;
    public $order_field;
    public $order_direction;
    
    private $all_sources;
    private $session_name = 'connections_log';
    
    private $start_timestamp;
    private $end_timestamp;

    public static function model($className=__CLASS__)
	{
        return parent::model($className);
    }    
    
    public function init()
	{
        $this->getFromMemory();
        
        $criteria = new CDbCriteria();
        $criteria->order = "communication_port asc, communication_type asc";

        $stations = Station::model()->findAll($criteria);
 		
        if (count($stations) > 0)
		{
            foreach ($stations as $station)
			{
                switch($station->communication_type)
				{
					case 'direct':
					case 'sms':
						
						$connection_type = $station->communication_port;
						break;
					
					case 'tcpip':
						
						$connection_type = $station->communication_esp_ip .':'. $station->communication_esp_port;
						break;
					
					case 'gprs':
						
						$connection_type = 'poller:'. $station->station_id_code;
						break;
					
					case 'server':
						
						$connection_type = 'tcp:'. $station->communication_esp_ip .':'. $station->communication_esp_port;
						break;
				}
				
                $this->all_sources[$connection_type] = $connection_type;
            }
        }        

        return parent::init();
    }
    
    //===== validation
    public function rules()
	{
        return array(
            array('source', 'length', 'allowEmpty' => false),
            array('date_from,date_to', 'match', 'pattern' => '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/'),
            array('date_from,date_to', 'length', 'max' => 10),
            array('time_from,time_to', 'match', 'pattern' => '/^(\d{1,2}):(\d{1,2})$/'),
            array('time_from,time_to', 'length', 'max' => 5),
            array('date_to', 'checkDatesInterval'),
        );
    }    
    
    public function checkDatesInterval()
	{
        if (!$this->hasErrors('date_from') && !$this->hasErrors('date_to') && !$this->hasErrors('time_from') && !$this->hasErrors('time_to'))
		{
            $this->start_timestamp = strtotime($this->date_from .' '. $this->time_from);
            $this->end_timestamp = strtotime($this->date_to .' '. $this->time_to);
			
            if ($this->end_timestamp <= $this->start_timestamp)
			{
                $this->addError('date_to', 'End date and time must be later than start.');
            }
        }
    }

    //======
    
    //==== work with memory
    public function putToMemory()
	{
        $session = new CHttpSession();
        $session->open();
 
        $session[$this->session_name] = array(
			'source'          => $this->source,
			'date_from'       => $this->date_from,
			'date_to'         => $this->date_to,
			'time_from'       => $this->time_from,
			'time_to'         => $this->time_to,        
			'order_field'     => $this->order_field,
			'order_direction' => $this->order_direction
		);
    }
    
    public function clearMemory()
	{
        $session = new CHttpSession();
        $session->open();
		
        $session[$this->session_name] = array();
        
		$this->getFromMemory();
    }
    
    public function getFromMemory()
	{
        $session = new CHttpSession();
        
        $session->open();
        
        $this->source          = $session[$this->session_name] ? $session[$this->session_name]['source'] : 0;
        $this->date_from       = $session[$this->session_name] ? $session[$this->session_name]['date_from'] : '';
        $this->date_to         = $session[$this->session_name] ? $session[$this->session_name]['date_to'] : '';
        $this->time_from       = $session[$this->session_name] ? $session[$this->session_name]['time_from'] : '00:00';
        $this->time_to         = $session[$this->session_name] ? $session[$this->session_name]['time_to'] : '23:59';        
        $this->order_field     = $session[$this->session_name] ? $session[$this->session_name]['order_field'] : 'date';
        $this->order_direction = $session[$this->session_name] ? $session[$this->session_name]['order_direction'] : 'DESC';
    }
    //==== 
        
   
    public function getAllSources()
	{
        return $this->all_sources;
    }
        
    public function setSource($source)
	{
        $this->source = $source;
        $this->putToMemory();
    }

    public function setOrders($field)
	{
        if (in_array($field, array('name', 'date', 'lasttx', 'lasthr', 'last24hr')))
		{
            if ($field == $this->order_field) 
			{
                $this->order_direction = $this->order_direction == 'ASC' ? 'DESC' : 'ASC';
            } 
			else 
			{
                $this->order_direction = 'ASC';
            }
			
            $this->order_field = $field;    
            $this->putToMemory();
        }
    }
    
    
    public function prepareList($page_size = 50)
	{
		$criteria = new CdbCriteria();

		$criteria->with = array('listener');

		if ($this->date_from)
		{
			$criteria->compare('listener.created', '>='. date('Y-m-d H:i:s', strtotime($this->date_from.' '.$this->time_from)));
		}

		if ($this->date_to)
		{
			$criteria->compare('listener.created', '<='. date('Y-m-d H:i:s', strtotime($this->date_to .' '.$this->time_to)));
		}

		$criteria->compare('listener.source', $this->source);

		$criteria->order = 'listener.created desc, t.created desc';

		$provider = new CActiveDataProvider(ListenerProcess, array(
				'criteria' => $criteria,
			
				'sort' => array(
					'defaultOrder' => array('listener.created' => true, 'created' => false),					
				),

				'pagination' => array(
					'pageSize' => empty($page_size) ? 10000 : $page_size,
				),
			)
		); 

		return $provider;    
    }
    
    public function exportList()
	{
        $connectionsLog = $this->prepareList(0);
        
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');

        // Turn off our amazing library autoload 
        // ( http://www.yiiframework.com/wiki/101/how-to-use-phpexcel-external-library-with-yii/ )
		// Choosed second solution
        //spl_autoload_unregister(array('YiiBase','autoload'));        

        // making use of our reference, include the main class
        // when we do this, phpExcel has its own autoload registration
        // procedure (PHPExcel_Autoloader::Register();)
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
 
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("Delairco")
        ->setLastModifiedBy("Delairco")
        ->setTitle("Connections Log Export")
        ->setSubject("Connections Log Export")
        ->setDescription("Was generated with Weather Monitor software.");        
        
        $objPHPExcel->createSheet($i);

        $objPHPExcel->setActiveSheetIndex($i);  
        //$objPHPExcel->getActiveSheet()->setTitle('Connections Log');        
        
        $row = 1;
        $col = 0;
		
        if ($connectionsLog->totalItemCount > 0)
		{   
			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimensionByColumn(0)->setWidth(15);
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimensionByColumn(1)->setWidth(20);
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimensionByColumn(2)->setWidth(100);
            
			$prev_listener_id = 0; 
			
            foreach ($connectionsLog->getData() as $record)
			{ 
                if ($record->listener_id != $prev_listener_id) 
				{
                    $row++;
                    $col = 0;
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, '**************');
                    $col++;
                    $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(1, $row, 2, $row);
                    
                    $str = "Connection #". $record->listener_id ." (". date('m/d/Y H:i:s', $record->listener->started) ." - ". ($record->listener->stopped ? date('m/d/Y H:i:s', $record->listener->stopped) : 'still connected') .")";
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $str);
                
                    $prev_listener_id = $record->listener_id;
                    $row++;
                }
				
                $col = 0;
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, date('m/d/Y H:i', strtotime($record->created)));
                
				$col++;
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $record->status);
                
				$col++;
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $record->comment);

                $row++;
            } 
       }

        // Set active sheet index to the first sheet, 
        // so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/xls');
        header('Content-Disposition: attachment;filename="ConnectionsLogExport.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        Yii::app()->end();

        // 
        // Once we have finished using the library, give back the 
        // power to Yii... 
        //spl_autoload_register(array('YiiBase','autoload'));
    }
    
    public function attributeLabels() {
         return array(
             'source' => Yii::t('project', 'Select Source:'),
             'date_from'  => Yii::t('project', 'Start Date'),
             'date_to'    => Yii::t('project', 'End Date:'),
             'time_from'  => Yii::t('project', 'Time:'),
             'time_to'    => Yii::t('project', 'Time:'),
         );
     }
     
    public function afterValidate() {
         $this->putToMemory();
         parent::afterValidate();
    }
}
?>