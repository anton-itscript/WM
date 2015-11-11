<?php

class m151005_095245_ftp_config_for_heartBeat extends CDbMigration
{
    protected $table="config";
    protected $heartbeat_report_table="heartbeat_report";

	public function safeUp()
	{
        $this->insert($this->table, array('key'=>'HEARTBEAT_REPORT_FTP', 'label'=>'Report to FTP', 'value'=>'', 'default'=>'', 'type'=>'string'));
        $this->insert($this->table, array('key'=>'HEARTBEAT_REPORT_FTP_PORT', 'label'=>'FTP Port', 'value'=>'', 'default'=>'', 'type'=>'string'));
        $this->insert($this->table, array('key'=>'HEARTBEAT_REPORT_FTP_DIR', 'label'=>'FTP folder', 'value'=>'', 'default'=>'', 'type'=>'string'));
        $this->insert($this->table, array('key'=>'HEARTBEAT_REPORT_FTP_USER', 'label'=>'FTP user', 'value'=>'', 'default'=>'', 'type'=>'string'));
        $this->insert($this->table, array('key'=>'HEARTBEAT_REPORT_FTP_PASSWORD', 'label'=>'FTP password', 'value'=>'', 'default'=>'', 'type'=>'string'));
        $this->addColumn($this->heartbeat_report_table,'ftp_status','VARCHAR(255)');
    }

	public function safeDown()
	{
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="HEARTBEAT_REPORT_FTP"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="HEARTBEAT_REPORT_FTP_PORT"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="HEARTBEAT_REPORT_FTP_DIR"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="HEARTBEAT_REPORT_FTP_USER"');
        $this->execute('DELETE FROM ' . $this->table . ' WHERE `key`="HEARTBEAT_REPORT_FTP_PASSWORD"');
        $this->dropColumn($this->heartbeat_report_table,'ftp_status');
     
	}

}