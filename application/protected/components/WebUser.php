<?php

class WebUser extends CWebUser
{
    protected $_model;
    protected $_settings_model;
    
	public $timezone_id;
    
    
    // This is a function that checks the field 'role'
    // in the User model to be equal to 1, that means it's admin
    // access it by Yii::app()->user->isAdmin()
    public function isAdmin()
    {

        $user = $this->loadUser();
        
		if ($user)
        {
			return $user->role == 'admin';
		}
        
		return false;
    }
	
    public function isSuperAdmin()
    {
        $user = $this->loadUser();
        
		if ($user)
        {
			return $user->role == 'superadmin';
		}
        
		return false;
    }

    public function loadUser()
    {
        if( $this->_model === null)
		{
            $this->_model = User::model()->findByPk(Yii::app()->user->id);
        }

        return $this->_model;
    }

    public function get($name)
	{
        $user = $this->loadUser();
        
		if ($user)
        {
			return $user->$name;
		}
		
        return null;
    }

    public function loadSettings()
	{
        if( $this->_settings_model === null)
		{
            $this->_settings_model = Settings::model()->findByPk(1);
        }

        return $this->_settings_model;        
    }
    
    public function getSetting($name)
	{
        $model = $this->loadSettings();
        
		if ($model)
        {
			return $model->$name;
		}
		
        return null;
    }    

    public function setTZ($tz)
    {
        $this->_settings_model->timezone_id = $tz;
        Settings::model()->updateByPk(1, array('local_timezone_id' => $tz, 'local_timezone_offset' => TimezoneWork::getOffsetFromUTC($tz, 1)));
    }

    public function getTZ()
    {
        return $this->getSetting('local_timezone_id');
    }
    
    /*
     * $unit:  
     * 's' - seconds
     * 'm' - minutes
     * 'h' - hours
     */
    public function getTZOffset($unit = 's')
	{
        $tz = $this->getTZ();
        $offset = TimezoneWork::getOffsetFromUTC($tz);
        
		if ($unit == 'm')
        {
			return $offset/60;
		}
		else if ($unit == 'h')
        {
			return $offset/3600;
		}
        
        return $offset;
    }
    /*
     * check access action
     */
    public function ckAct($controller, $action){

        if ($controller == 'superadmin') {
            return true;
        }

        if (isset($this->access)) {

            $accessArray = array();
            foreach ($this->access as $controller_ => $access_) {

                foreach ($access_ as $action_) {
                    $accessArray[strtolower($controller_)][] = strtolower($action_);
                }
            }
            $this->access = $accessArray;
            return in_array($action,$this->access[$controller]);
        }
    }
}

?>