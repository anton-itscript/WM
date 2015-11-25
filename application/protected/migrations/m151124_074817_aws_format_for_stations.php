<?php

class m151124_074817_aws_format_for_stations extends CDbMigration
{
	protected $table='station';
	protected $column='aws_format';
	protected $type='int(10)NOT NULL DEFAULT 1';


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