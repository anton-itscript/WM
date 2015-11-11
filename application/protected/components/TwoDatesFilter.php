<?php

class TwoDatesFilter extends CWidget {

    var $block_path;
    var $date_from_name;
    var $date_to_name;
    var $load_js = true;
    
    public function init() {}

    public function run() {
        Yii::app()->clientScript->registerPackage('jquery.datePicker');
        $this->render('TwoDatesFilter');
    }
}

?>