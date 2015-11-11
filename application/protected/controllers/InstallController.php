<?php

	/**
	 * Controller for install process. 
	 */
	class InstallController extends CController 
	{
		public $layout = 'install';

		/**
		 *  First page. Check COM port component is installed. 
		 */
		public function actionIndex() 
		{
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
            $conf_form = new InstallConfig;
            $conf_form_long = new AdminConfig;


			if (Yii::app()->request->isPostRequest && isset($_POST['save_db_config'])) {
				$conf_form->scenario = 'database';
				$conf_form->attributes = $_POST['InstallConfig'];

				if ($conf_form->validate()) {
					$conf_form->saveDBConfig();

				}
			}

            if (Yii::app()->request->isPostRequest && isset($_POST['save_db_config_long'])) {
                $conf_form_long->scenario = 'DB';
                $conf_form_long->attributes = $_POST['AdminConfig'];

				if ($conf_form_long->validate()) {
                    $conf_form_long->saveDBConfig();
                    $conf_form_long->deleteSync();

				}
			}
			
			$conf_form->getAvailableStep();


            if (Yii::app()->request->isPostRequest && $conf_form->available_step > 1) {
				$this->redirect($this->createUrl('install/step2'));
			}

            $this->render('index', array(
				'conf_form' => $conf_form,
				'conf_form_long' => $conf_form_long,
			));
		}

		public function actionStep2()
		{
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
			$conf_form = new InstallConfig;
            $conf_form_long = new AdminConfig;


			if ($conf_form->available_step < 2) 
			{
				$this->redirect($this->createUrl('install/index'));
			}      

			if (Yii::app()->request->isPostRequest && isset($_POST['create_database']))
			{
				$res = $conf_form->setupDb();
				$res_long = $conf_form_long->setupDb();

				if (isset($res['ok']) && isset($res_long['ok'])) {
					sleep(1);
					$conf_form->saveDBInstallStatusConfig(1);
					$this->redirect($this->createUrl('install/step3'));
				} else {
					print_r($res);
					print_r($res_long);
				}


			}
			
			$conf_form->getAvailableStep();
			
			$this->render('step2', array(
				'conf_form' => $conf_form,
			));        
		}

		public function actionStep3()
		{
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
			$conf_form = new InstallConfig;
            $conf_form_long = new AdminConfig;


			if ($conf_form->available_step < 3)
			{
				$this->redirect($this->createUrl('install/step2'));
			}     

			if (Yii::app()->request->isPostRequest && isset($_POST['save_path']))
			{
				$conf_form->scenario = 'path';
				$conf_form->attributes = $_POST['InstallConfig'];

				if ($conf_form->validate()) {
					$conf_form->savePathConfig();
                    $conf_form_long->deleteSync();
					$this->redirect($this->createUrl('install/step4'));
				}
			}        

			$conf_form->getAvailableStep();
			
			$this->render('step3', array(
				'conf_form' => $conf_form,
			));           
		}

		public function actionStep4()
		{
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
			$conf_form = new InstallConfig();
            $conf_form_long = new AdminConfig;
			$conf_form->getAvailableStep();

			if ($conf_form->available_step < 4){
				$this->redirect($this->createUrl('install/step3'));
			} 

			if (Yii::app()->request->isPostRequest && isset($_POST['schedule'])){
				$conf_form->setSchedule();
                $conf_form_long->deleteSync();
                $conf_form_long->createSync();
			}

			$conf_form->getAvailableStep();
			
			$this->render('step4', array(
				'conf_form' => $conf_form,
				'conf_form_long' => $conf_form_long,
			));
		}
	}
?>
