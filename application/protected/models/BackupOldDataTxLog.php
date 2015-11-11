<?php

class BackupOldDataTxLog extends CStubActiveRecord
{
    public static function model($className=__CLASS__)
	{
        return parent::model($className);
    }

    public function tableName()
	{
        return 'backup_old_data_log';
    }
    
    public function beforeSave()
	{
        if ($this->isNewRecord)
		{
            $this->created = new CDbExpression('NOW()');
        }
		
        return parent::beforeSave();
    }

    public function prepareList($page_size = 5)
	{
		if ($page_size)
		{
			$sql = "SELECT COUNT(*)
					FROM `".  BackupOldDataTx::model()->tableName()."` 
					ORDER BY `backup_date` DESC";

			$total = Yii::app()->db->createCommand($sql)->queryScalar();

			$pages = new CPagination($total);
			$pages->pageSize = $page_size;
			//$pages->applyLimit($criteria);
		}

		$sql = "SELECT *
				FROM `".BackupOldDataTx::model()->tableName()."` 
				ORDER BY `backup_date` DESC";

		if ($page_size) {
			$sql .= " LIMIT ".($pages->currentPage *$pages->pageSize).", ".$pages->pageSize;
		}
		$res = Yii::app()->db->createCommand($sql)->queryAll();
		if ($res) {
			$ids = array();
			foreach ($res as $key => $value) {
				$ids[] = $value['id'];
			}

			$sql = "SELECT * 
					FROM `".BackupOldDataTxLog::model()->tableName()."` 
					WHERE `backup_id` IN (".implode(',', $ids).") 
					ORDER BY `created` ASC";
			$res2 = Yii::app()->db->createCommand($sql)->queryAll();
			if ($res2) {
				foreach ($res2 as $key => $value) {
					$res2prepared[$value['backup_id']][] = $value;
				}

				foreach ($res as $key => $value) {
					$res[$key]['logs'] = $res2prepared[$value['id']] ? $res2prepared[$value['id']] : array();
				}
			}
		}
        
        return array('list' => $res, 'pages' => $pages);    
    }
}