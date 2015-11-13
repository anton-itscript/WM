<?php
class ColorValidator extends CValidator{

    public function validateAttribute($model, $attribute) {

        $value = $model->$attribute;
        $place = substr($value,0,1);
        $hexColor = substr($value,1,6);

        if (!ctype_xdigit($hexColor) or $place!='#' or (strlen($hexColor)!=6 and strlen($hexColor)!=3) ) {
            $this->addError($model,$attribute,Yii::t('validators_messages','it is not color'));
        }

    }
}
?>