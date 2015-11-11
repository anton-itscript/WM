<?php


class AccessUser extends CStubActiveRecord
{

	public static function model($className=__CLASS__){
		return parent::model($className);
	}
	public function tableName(){
		return 'access_user';
	}
    /*
     * Check parameters
     */
    public function rules(){
        return array(
            array('user_id, action_id', 'required'),
        );
    }
    /*
     * Get action id
     */
    public static function getActionIdFromUser($user_id){
        $return_array = array();
        if ($user_id){
            $res = AccessUser::model()->findAllByAttributes(array('user_id'=>$user_id));
            if ($res){
                foreach($res as $val)
                    $return_array[]=$val['action_id'];
            }
        }
        return $return_array;
    }
    /*
     * Check action at user
     */
    public static function checkActionAtUser($user_id,$action_id){
        if($user_id and $action_id){
            $res = AccessUser::model()->findAllByAttributes(array('user_id'=>$user_id, 'action_id'=>$action_id));
            return !empty($res);
        }
        return false;
    }
}