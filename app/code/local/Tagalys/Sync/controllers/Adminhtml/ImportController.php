<?php
require_once 'Mage/ImportExport/controllers/Adminhtml/ImportController.php';

class Tagalys_Sync_Adminhtml_ImportController extends Mage_ImportExport_Adminhtml_ImportController
{

  public function validateAction() {
    	//[Custom Changes]
    	// Mage::log("CSV Import: Validation process started", null, "tagalys.log");
    parent::validateAction();
  }

  public function startAction() {
    	// Mage::log("CSV Import: Data import process started", null, "tagalys.log");
    $data = $this->getRequest()->getPost();
    if ($data) {
      $this->loadLayout(false);

      /** @var $resultBlock Mage_ImportExport_Block_Adminhtml_Import_Frame_Result */
      $resultBlock = $this->getLayout()->getBlock('import.frame.result');
      /** @var $importModel Mage_ImportExport_Model_Import */
      $importModel = Mage::getModel('importexport/import');

      try {
        $importModel->importSource();
        $importModel->invalidateIndex();
        $resultBlock->addAction('show', 'import_validation_container')
        ->addAction('innerHTML', 'import_validation_container_header', $this->__('Status'));
      } catch (Exception $e) {
      	//[Custom Changes]
      	// Mage::log("CSV Import: Data import process error", null, "tagalys.log");
        $resultBlock->addError($e->getMessage());
        $this->renderLayout();
        return;
      }
      //[Custom Changes]
      // Mage::log("CSV Import: Data import process success", null, "tagalys.log");
      $resultBlock->addAction('hide', array('edit_form', 'upload_button', 'messages'))
      ->addSuccess($this->__('Import successfully done.'));
      $this->renderLayout();
    } else {
    	//[Custom Changes]
    	// Mage::log("CSV Import: Record not found", null, "tagalys.log");
      $this->_redirect('*/*/index');
    }

     Mage::dispatchEvent('catalog_product_import_profile_after', array('adapter' => $this));
    return true;
  }

}