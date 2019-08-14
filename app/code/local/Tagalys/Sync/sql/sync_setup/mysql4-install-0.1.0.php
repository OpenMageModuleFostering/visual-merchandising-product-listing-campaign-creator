<?php
$installer = $this;
$installer->startSetup();
$tagalys_queue = $installer->getConnection()->newTable($installer->getTable('tagalys_queue'))
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
$installer->getConnection()->createTable($tagalys_queue);
$installer->endSetup();
	 