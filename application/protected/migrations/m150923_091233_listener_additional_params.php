<?php

class m150923_091233_listener_additional_params extends CDbMigration
{
    protected $table = "listener";
    protected $column = "additional_param";
    protected $type = "VARCHAR (255)";


	public function safeUp()
	{
        $this->addColumn($this->table, $this->column, $this->type);
	}

	public function safeDown()
	{
        $this->dropColumn($this->table, $this->column);

	}

}