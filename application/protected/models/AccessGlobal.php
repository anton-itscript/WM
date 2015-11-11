<?php


class AccessGlobal extends CStubActiveRecord
{

	public static function model($className=__CLASS__){
		return parent::model($className);
	}

	public function tableName(){
		return 'access_global';
	}
    /*
     * Check parameters
     */
    public function rules(){
        return array(
            array('action, controller, enable', 'required'),
            array('enable', 'in', 'range' => array('1', '0')),
            array('controller','checkController'),
            array('action','checkAction'),
            array('description','length','max' => 255)
        );
    }
    public function checkController(){
        if(in_array($this->controller,array_slice(Yii::app()->params['controllers'],1)))
            return true;
        $this->addError('controller', 'Controller not found. Use '.implode(', ',array_slice(Yii::app()->params['controllers'],1)) );
        return false;
    }
    public function checkAction(){
        $key=AccessGlobal::getActionIdForController($this->action,$this->controller);

        if(!$key or $key == $this->id)
            return true;

        $this->addError('action', 'Action already use.');
        return false;
    }

    /*
     * ACTION
     */
    public static function getActionIdForController($action,$controller){
        if ($controller){
            $sql_where = "WHERE `controller` = '".$controller."' AND `action` = '".$action."'";
            $sql = "SELECT `id`
                FROM `".AccessGlobal::model()->tableName()."`
                {$sql_where}";

            $res = Yii::app()->db->createCommand($sql)->queryAll();

            if ($res){
                return $res[0]['id'];
            }
        }
        return false;
    }
    public static function getActionForController($controller,$enable = 1,$disable = 0){
        $action = array();
        if ($controller){
            $sql_where = "WHERE `controller` = '".$controller."' AND `enable` IN ('".$enable."','".$disable."')";
            $sql = "SELECT `action`
                FROM `".AccessGlobal::model()->tableName()."`
                {$sql_where}";

            $res = Yii::app()->db->createCommand($sql)->queryAll();

            if ($res){
                foreach ($res as $value){
                    $action[] = strtolower($value['action']);
                }
            }
        }
        return $action;
    }
    public static function getActionFromArrayId($arrayId){
        $sql = AccessGlobal::model()->findAllByAttributes(array('id'=>$arrayId));
        $res = array();
        foreach($sql as $action){
            $res[]=strtolower($action['action']);
        }
        return $res;
    }

    public static function getAction($disable){
        $action = array();

        $controllers = Yii::app()->params['controllers'];
        foreach ($controllers as $controller){
            $temp = AccessGlobal::getActionForController($controller,1,$disable);
            if($temp) $action[$controller] = $temp;
        }

        return $action;
    }
    /*
 * Default action
 */
    public static function getDefaultAction(){
        return array('Logout','Login','Index');
    }
    public static function getIdDefaultAction(){
        $res = array();
        $defAction = AccessGlobal::getDefaultAction();
        foreach($defAction as $key => $action){
            $res[$key]=AccessGlobal::getActionIdForController($action,'Site');
        }
        return $res;
    }
}