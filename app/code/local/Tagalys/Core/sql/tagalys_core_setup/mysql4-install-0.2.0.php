<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

	-- DROP TABLE IF EXISTS {$this->getTable('tagalys_core_config')};
	CREATE TABLE {$this->getTable('tagalys_core_config')} (
		`config_id` int(10) unsigned NOT NULL auto_increment,
		`path` varchar(255) NOT NULL default 'general',
		`value` text NOT NULL,
		PRIMARY KEY  (`config_id`),
		UNIQUE KEY `config_scope` (`path`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();
