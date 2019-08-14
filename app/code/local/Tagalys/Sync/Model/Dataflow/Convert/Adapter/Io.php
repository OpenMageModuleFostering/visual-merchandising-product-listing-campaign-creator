<?php

class Tagalys_Sync_Model_Dataflow_Convert_Adapter_Io extends Mage_Dataflow_Model_Convert_Adapter_Io 
{
	    public function save() {

	    	try {
            	$bulkimport = new Mage_Core_Model_Config();
           		$bulkimport->saveConfig('sync/product/import_status', "disable", "default", "disable");
        	} catch (Exception $e) {
            
        	}     
        	
	    	return parent::save();
	    }

}