<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity {
    private $_id;
    public function authenticate()
    {
        $record = User::model()->findByAttributes(array('username' => $this->username));

        if ($record === null) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } else if (!CPasswordHelper::verifyPassword($this->password,$record->password)) {
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        } else {
            $access = AccessGlobal::getAction($record->role == 'superadmin'?'0':'1');
            if($record->role == 'user'){
                $access['site']=array_intersect($access['site'],AccessGlobal::getActionFromArrayId(AccessUser::getActionIdFromUser($record->user_id)));
                $access['sdmin']=array('');
            }
            $this->_id = $record->user_id;
            $this->setState('role', $record->role);
            $this->setState('name', $this->username);
            $this->setState('access', $access);
            $this->errorCode = self::ERROR_NONE;
        }
        return !$this->errorCode;
    }

    public function getId(){
        return $this->_id;
    }


}