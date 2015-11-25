<?php

class m151124_074931_aws_format_for_stations_long extends CDbMigration
{
	protected $_db;

	protected $table = 'station';
	protected $column = 'aws_format';
	protected $type = 'int(10) NOT NULL DEFAULT 1';

	public function getDbConnection()
	{

		$this->_db = Yii::app()->db_long;
		return $this->_db;
	}

	public function setDbConnection($db)
	{
		$this->_db = $db;
	}


	public function safeUp()
	{
		$this->addColumn($this->table, $this->column, $this->type);
	}

	public function safeDown()
	{
		$this->dropColumn($this->table, $this->column);
		return true;
	}
}