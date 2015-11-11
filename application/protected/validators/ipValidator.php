<?php
class ipValidator extends CValidator{

    public $array_key=false;

	public function validateAttribute($model, $attribute) {
        $value = $model->$attribute;

        if($this->array_key){
            $value = $model->{$attribute}[$this->array_key];
        }

        if(!preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $value)){
            $this->addError($model,$attribute,Yii::t('validators_messages','IP address is NOT valid!'));
        }

	}
}
?>