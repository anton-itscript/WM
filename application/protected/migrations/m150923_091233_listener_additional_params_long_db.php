<?php

class m150923_091233_listener_additional_params_long_db extends CDbMigration
{
    protected $table = "listener";
    protected $column = "additional_param";
    protected $type = "VARCHAR (255)";

    protected $_db;

    public function getDbConnection()
    {

        $this->_db= Yii::app()->db_long;
        return $this->_db;
    }

    public function setDbConnection($db)
    {
        $this->_db=$db;
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