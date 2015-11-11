<?php

	class ThrowStatus extends CWidget 
	{
		public function init() 
		{

		}

		public function run() 
		{
			$status_code = array();
			
			if (isset($_SESSION['status_code']))
			{
				if (is_array($_SESSION['status_code'])) 
				{
					$status_code = $_SESSION['status_code'];
				}
				else
				{
					$status_code[] = $_SESSION['status_code'];
				}
				
				unset($_SESSION['status_code']);
			}

			$this->render('ThrowStatus', array(
				'status_code' => $status_code,
			));
		}
	}
?>