<?php
$installer = $this;
$installer->startSetup();
 $installer->run("

                     -- DROP TABLE IF EXISTS {$this->getTable('tagalys_queue')};
                     CREATE TABLE {$this->getTable('tagalys_queue')} (
                                                                      `product_id` varchar(50) NOT NULL default 'general'
                                                                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->run("
	ALTER TABLE {$this->getTable('tagalys_queue')}
	MODIFY `product_id` varchar(50) NULL;
	");
 
$installer->endSetup();
