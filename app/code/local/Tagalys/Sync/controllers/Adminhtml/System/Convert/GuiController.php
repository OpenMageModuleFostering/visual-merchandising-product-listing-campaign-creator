<?php
require_once 'Mage/Adminhtml/controllers/System/Convert/GuiController.php';
/**
 * Override Admin controller
 */
class Tagalys_Sync_Adminhtml_System_Convert_GuiController extends Mage_Adminhtml_System_Convert_GuiController 
{
	public function runAction() {

		try {
			$bulkimport = new Mage_Core_Model_Config();
			$bulkimport->saveConfig('sync/product/import_status', "enable", "default", "disable");
		} catch (Exception $e) {
			Mage::log("TagalysGuiControllerException".print_r($e->getMessage()), null, "tagalys-exception.log");
		}

		parent::runAction();
	}

	public function batchFinishAction() {
		try {
			$bulkimport = new Mage_Core_Model_Config();
			$bulkimport->saveConfig('sync/product/import_status', "disable", "default", "disable");
		} catch (Exception $e) {
			Mage::log("TagalysGuiControllerException".print_r($e->getMessage()), null, "tagalys-exception.log");
		}
		parent::batchFinishAction();
	}
}