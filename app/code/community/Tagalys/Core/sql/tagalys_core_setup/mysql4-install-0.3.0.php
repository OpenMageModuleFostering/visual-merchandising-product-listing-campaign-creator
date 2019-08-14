<?php
$installer = $this;

$installer->startSetup();

$installer->run("DROP TABLE IF EXISTS {$this->getTable('tagalys_queue')}");
$installer->run("DROP TABLE IF EXISTS {$this->getTable('tagalys_core_config')}");
$installer->run("DROP TABLE IF EXISTS {$this->getTable('tagalys_core_queue')}");
Mage::getModel('core/config_data')->load('crontab/jobs/tagalys_updates_cron/schedule/cron_expr', 'path')->delete();
Mage::getModel('core/config_data')->load('crontab/jobs/tagalys_resync_cron/schedule/cron_expr', 'path')->delete();

$tagalys_core_config_table = $installer->getConnection()->newTable($installer->getTable('tagalys_core_config'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'identity' => true,
        ), 'ID')
    ->addColumn('path', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
        'default' => '-',
        ), 'Config Path')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => false,
        'default' => '',
        ), 'Config Value');
$tagalys_core_config_table->addIndex(
  $installer->getIdxName('tagalys_core/config', array('path'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
  array('path'),
  array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
);
$installer->getConnection()->createTable($tagalys_core_config_table);

$tagalys_core_queue_table = $installer->getConnection()->newTable($installer->getTable('tagalys_core_queue'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'identity' => true,
        ), 'ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 50, array(
        'nullable' => false,
        'default' => '0', 
        ), 'Product ID')  
    ->setComment('Tagalys Product Queue Table');
$installer->getConnection()->createTable($tagalys_core_queue_table);

$installer->endSetup();
