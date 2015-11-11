<?php

class m151016_052341_add_color_column_long_db extends CDbMigration
{
	protected $_db;

	protected $table = 'station';
	protected $column = 'color';
	protected $type = 'VARCHAR(255)';

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