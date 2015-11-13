<?php

class m151014_045857_add_color_column extends CDbMigration
{
	protected $table='station';
	protected $column='color';
	protected $type='VARCHAR(255)';


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