<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
  public function __construct() {

    $this->_objectId    = 'id';
    $this->_controller  = 'adminhtml_tagalys';
    $this->_blockGroup  = 'tagalys_core';
    parent::__construct();

    $this->_removeButton('save');
    $this->_removeButton('back');
    $this->_removeButton('reset');
    
  }

  /**
   * Get header text
   *
   * @return string
   */
  public function getHeaderText() {

   $status = Mage::helper('tagalys_core')->getTagalysConfig('setup_complete');
   if($status) {
      return $this->__('Tagalys Configuration');
   }
    return $this->__('Initial Setup');
  }

  /**
   * Check permission for passed action
   *
   * @param string $action
   * @return bool
   */
  protected function _isAllowedAction($action) {
    return true;
  }
}
