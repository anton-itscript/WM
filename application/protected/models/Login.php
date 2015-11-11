<?php

class Login extends CFormModel {
    public $rememberMe;
    public $username;
    public $password;


    public function rules() {
        return array(
            array('username,password', 'required'),
            array('password', 'authenticate'),
            array('rememberMe', 'boolean', 'trueValue' => 1, 'falseValue' => 0)
        );
    }

    /**
     * Authenticates the password.
     * This is the 'authenticate' validator as declared in rules().
     */
    public function authenticate($attribute, $params) {
        if (!$this->hasErrors()) {  // we only want to authenticate when no input errors
            $identity = new UserIdentity($this->username, $this->password);
            $identity->authenticate();
            switch ($identity->errorCode) {
                case UserIdentity::ERROR_NONE:
                    $duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
                    Yii::app()->user->login($identity, $duration);
                    break;
                case UserIdentity::ERROR_USERNAME_INVALID:
                    $this->addError('username', Yii::t('project', 'A user with this name is not registered. Please register first.'));
                    break;
                default: // UserIdentity::ERROR_PASSWORD_INVALID
                    $this->addError('password', Yii::t('project', 'Password is incorrect.'));
                    break;
            }
        }
    }

    public function attributeLabels() {
        return array(
            'username' => Yii::t('project', 'User'),
            'password' => Yii::t('project', 'Password'),
            'rememberMe' => Yii::t('project', 'Save'),
        );
    }
}