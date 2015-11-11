<?php

class BackupOldDataTx extends CStubActiveRecord
{
    public static function model($className=__CLASS__)
	{
        return parent::model($className);
    }

    public function tableName()
	{
        return 'backup_old_data';
    }
    
    public static function getLastBackupInfo()
	{
        $criteria = new CDbCriteria();
        $criteria->order = 'backup_date DESC';
        $criteria->limit = 1;

		return BackupOldDataTx::model()->find($criteria);
    }
}