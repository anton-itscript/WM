<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 13.01.15
 * Time: 13:34
 */

// TODO
class CoefficientsForm extends CFormModel
{
    /** @var  array */
    protected $coefficients;

    /** @var  array */
    protected $description;

    public function init(){
        $this->coefficients = array(
            'PAtMSL' => '',
        );
        $this->description = array(
            'PAtMSL' => 'Pressure Adjusted to Mean Sea Level',
        );
    }

    public function rules(){
        return array(
            'coefficients' => 'checkCoefficients'
        );
    }

    public function checkCoefficient(){
        return true;
    }

} 