<?php

class m150327_063204_sync_settings extends CDbMigration
{
    protected $table="config";
	public function up()
	{
        $this->insert($this->table, array('key'=>'SYNC_SERVER_IP', 'label'=>'IP address', 'value'=>'192.168.1.1', 'default'=>'10.10.10.10', 'type'=>'string'));
        $this->insert($this->table, array('key'=>'SYNC_SERVER_PORT', 'label'=>'Port', 'value'=>'4523', 'default'=>'80', 'type'=>'int'));
        $this->insert($this->table, array('key'=>'SYNC_REMOTE_SERVER_IP', 'label'=>'Remote server IP address', 'value'=>'192.168.1.1', 'default'=>'10.10.10.10', 'type'=>'string'));
        $this->insert($this->table, array('key'=>'SYNC_REMOTE_SERVER_PORT', 'label'=>'Remote server Port', 'value'=>'4523', 'default'=>'80', 'type'=>'int'));
        $this->insert($this->table, array('key'=>'SYNC_SWITCH_VARIANT', 'label'=>'Switch variant', 'value'=>'1', 'default'=>'1', 'type'=>'int'));
        $this->insert($this->table, array('key'=>'SYNC_FLEXIBILITY_ROLE', 'label'=>'Sync flexibility role', 'value'=>'2', 'default'=>'1', 'type'=>'int'));
        $this->insert($this->table, array('key'=>'SYNC_PROCESS_STATUS', 'label'=>'Process status', 'value'=>'0', 'default'=>'1', 'type'=>'int'));

        $this->insert($this->table, array('key'=>'SYNC_MAIN_ROLE', 'label'=>'Main role', 'value'=>'1', 'default'=>'1', 'type'=>'int'));
        $this->insert($this->table, array('key'=>'SYNC_FOR_COMES_FORWARDING_MESSAGES_IP', 'label'=>'Receiving messages IP', 'value'=>'0', 'default'=>'192.168.101.212', 'type'=>'string'));
        $this->insert($this->table, array('key'=>'SYNC_FOR_COMES_FORWARDING_MESSAGES_PORT', 'label'=>'Receiving messages  PORT', 'value'=>'0', 'default'=>'5910', 'type'=>'string'));
        $this->insert($this->table, array('key'=>'SYNC_FOR_SEND_MESSAGES_TO_IP', 'label'=>'Send messages IP', 'value'=>'0', 'default'=>'5910', 'type'=>'string'));
        $this->insert($this->table, array('key'=>'SYNC_FOR_SEND_MESSAGES_PORT', 'label'=>'Send messages  PORT', 'value'=>'0', 'default'=>'5910', 'type'=>'string'));

	}

	public function down()
	{
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="SYNC_SERVER_IP"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="SYNC_SERVER_PORT"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="SYNC_REMOTE_SERVER_IP"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="SYNC_REMOTE_SERVER_PORT"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="SYNC_SWITCH_VARIANT"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="SYNC_FLEXIBILITY_ROLE"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="SYNC_PROCESS_STATUS"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="SYNC_MAIN_ROLE"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="SYNC_FOR_COMES_FORWARDING_MESSAGES_IP"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="SYNC_FOR_COMES_FORWARDING_MESSAGES_PORT"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="SYNC_FOR_SEND_MESSAGES_TO_IP"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="SYNC_FOR_SEND_MESSAGES_PORT"');
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}