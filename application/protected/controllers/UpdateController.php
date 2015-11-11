<?php

class UpdateController extends CController
{
    function actionIndex()
    {
        $form = new UpdateScriptForm();
        
        if (Yii::app()->request->isPostRequest && $_POST['UpdateScriptForm']) {
            
            $form->file = CUploadedFile::getInstance($form, 'update_zip');
            
            if ($form->validate()) {
                $result = $form->processUpdate();
                
                $this->redirect($this->createUrl('update/CheckExtraUpdate'));
            } else {
                
            }

        }
        
        $file = dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR .'files' . DIRECTORY_SEPARATOR.'change_history.txt';
        if (file_exists($file)) {
            $history = file_get_contents($file);
        }
        
       
        $this->render('index', array(
            'form' => $form,
            'history' => $history
        ));
    }


    function actionCheckExtraUpdate() {

        ini_set('memory_limit', '-1');
        set_time_limit(0);
        
//        $version = getConfigValue('version');
        $version = array();
        $method_name = 'm_'.$version['stage'].'_'.$version['sprint'].'_'.$version['update'];
        $form = new UpdateScriptForm();
        if (method_exists($form, $method_name)) {
            $form->$method_name(Yii::app()->db);
            It::memStatus('update__success');
        }
        $this->redirect($this->createUrl('update/index'));
        //print ('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 500)</script>');        
    }
    
    public function filters()
    {
        return array(
            'accessControl',
        );
    }
    
    public function accessRules(){
        return array(
            array('allow',
                'users' => array('superadmin'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }
    
}