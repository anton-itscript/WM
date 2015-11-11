<?php

class Listener extends CStubActiveRecord
{





    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function beforeSave(){
        if(!$this->getUseLong()){
            if ($this->isNewRecord){
                $this->created = new CDbExpression('NOW()');
            }
            $this->updated = new CDbExpression('NOW()');
        }

        return parent::beforeSave();
    }

	public function tableName()
	{
		return 'listener';
	}

	public static function getCurrent($source, $type = false)
	{
	    $criteria = new CDbCriteria();
	    $criteria->condition = "source = :source AND stopped = '' ";
	    $criteria->params = array(':source' => $source);
	    if ($type!==false) {
            $criteria->condition .= " AND additional_param = :additional_param";
            $criteria->params[':additional_param'] = $type;
        } else {
            $type='';
        }
		$res = Listener::model()->find($criteria);
	    
		if (is_null($res))
		{
	        $res = new Listener();
	        $res->source = $source;
	        $res->additional_param = $type;
	    }
		
	    return $res;
	}


    public static function getCurrentAllConnetions()
    {
        $criteria = new CDbCriteria();
        $criteria->condition = " stopped = ''";
       // $criteria->params = array(':source' => $source);

        $res = Listener::model()->findAll($criteria);

        if (count($res)>0)
            return $res;

        return false;
    }

    public static function stopAllConnections()
    {
        $connections = self::getCurrentAllConnetions();

        if($connections)
            array_map($connections, function($connection){
                if (is_object($connection))
                    self::stopConnection($connection->listener_id);
            });

        return true;
    }

    public function checkPreparingProcess()
    {
		if (ProcessPid::isActive('process_message') === false)
		{
			$command = Yii::app()->params['applications']['php_exe_path'] . " -f " . Yii::app()->params['applications']['console_app_path'] . " prepare";

			It::runAsynchCommand($command);
		}      
    }


    public static function getLastConnectionInfoForSynch($source)
    {
        $sql = "SELECT * FROM `listener` WHERE UPPER(`source`) = UPPER(?) ORDER BY `created` DESC";
        $res = Yii::app()->db->createCommand($sql)->queryRow(true, array($source));

        if (!$res)
        {
            return null;
        }
        return $res;
    }

    public static function getLastConnectionInfo($source, $communication_type='')
    {
        $sql = "SELECT * FROM `".Listener::tableName()."` WHERE UPPER(`source`) = UPPER(?)";
        if ($communication_type !='') {
            $sql .=  "AND additional_param='".$communication_type."'";
        }

        $sql .= " ORDER BY `created` DESC";
        $res = Yii::app()->db->createCommand($sql)->queryRow(true, array($source));

        if (!$res)
        {
            return null;
        }

        $res['started_show'] = date('M jS, H:i', $res['started']);
		
        if (!$res['stopped'])
		{
            $res['duration'] = time() - $res['started'];
            $res['stopped_show'] = '';
        }
		else
		{
            $res['duration'] = $res['stopped'] - $res['started'];
            $res['stopped_show'] = date('M jS, H:i', $res['stopped']);
        }

        if ($res['duration'])
		{   
            $hour = floor($res['duration'] / 3600);

            //PHP Error[8]: Undefined index: duration_formatted line 123
            // i don't have idea about that
            if ($hour)
			{
				$res['duration_formatted'] = $hour .'hr';
            }
            
            $min = floor(($res['duration'] - $hour*3600) / 60);
            
            if ($min)
			{
				$res['duration_formatted'] .= ($res['duration_formatted'] ? ' ' : ''). $min .'min';
            }
            
            $sec = $res['duration'] - $min*60 - $hour*3600;
            
			if ($sec)
			{
				$res['duration_formatted'] .= ($res['duration_formatted'] ? ' ' : ''). $sec .'sec';
            }
        }
		else
		{
			$res['duration_formatted'] = '0 sec';
        }

        return $res;
    }

    public static function runConnection($source,$additional_param, $by = 'user')
	{
        $command = Yii::app()->params['applications']['php_exe_path'] ." -f ". Yii::app()->params['applications']['console_app_path'] ." listen ". $source ." ".$additional_param." ". $by;

		It::runAsynchCommand($command);
		
        return true;
    }
    
    public static function stopConnection($listener_id, $time = 0)
    {
        $time = $time ? $time : time();
		
        Listener::model()->updateByPk($listener_id, array('stopped' => $time));
        ListenerProcess::addComment($listener_id, 'comment', 'Stop time was saved');
        
		return true;
    }
}