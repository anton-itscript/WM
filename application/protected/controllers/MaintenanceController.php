<?php
	/**
	 * Controller that catches all requests. 
	 */
	class MaintenanceController extends CController
	{
		public function actionIndex()
		{
			// return maintenance to framework
			//Listener::checkPreparingProcess();

			$route = Yii::app()->getUrlManager()->parseUrl(Yii::app()->getRequest());
			
			if (strtolower($route) != strtolower('update/CheckExtraUpdate')) 
			{
				if (isset($_POST['change_timezone_id'])) 
				{
					Yii::app()->user->setTZ($_POST['change_timezone_id']);
				}

				$tz = Yii::app()->user->getTZ();

				// Set timezone in either case, because mysql timezone settings can differ from php
//				if ($tz != date_default_timezone_get()) 
//				{
					TimezoneWork::set($tz);
//				}
			}

			Yii::app()->runController($route);
		}
	}
?>