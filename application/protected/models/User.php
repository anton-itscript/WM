<?php


class User extends CStubActiveRecord
{
    public $pass='';
    public $pass2='';

	public static function model($className=__CLASS__){
		return parent::model($className);
	}

	public function tableName(){
		return 'user';
	}
    public function attributeLabels(){
        return array (
            'user_id'        => Yii::t('project', 'User ID'),
            'username'       => Yii::t('project', 'User Name'),
            'email'          => Yii::t('project', 'Email'),
            'pass'           => Yii::t('project', 'Password'),
            'role'           => Yii::t('project', 'Role'),
            'access'         => Yii::t('project', 'Access User'),
            'allAccess'      => Yii::t('project', 'All Access'),
        );
    }
    public function rules(){
        return array(
            array('username, email, role', 'required'),

            array('username, email', 'unique'),
            array('username', 'length', 'min'=>3, 'max'=>12),

            array('pass', 'length', 'max' => 12),
            array('pass', 'checkPass'),

            array('pass2', 'length', 'max' => 12),
            array('pass2', 'checkPass2'),
        );
    }
    public function checkPass(){
        if($this->user_id && $this->pass == '')
            return true;

        if(strlen($this->pass)<3 ){
            $this->addError('pass', 'len pass 3-12');
            return false;
        }

        return true;
    }
    public function checkPass2(){
        if($this->pass != $this->pass2 ){
            $this->addError('pass2', 'Password are not equal');
            return false;
        }

        return true;
    }
    public function beforeSave(){
        if ($this->isNewRecord){
            $this->created = new CDbExpression('NOW()');
        }
        $this->updated = new CDbExpression('NOW()');

        if($this->pass != '')
            $this->password = CPasswordHelper::hashPassword($this->pass);

        return parent::beforeSave();

    }

    public function isSuperAdmin()
    {
        return $this->role == 'superadmin';
    }

}