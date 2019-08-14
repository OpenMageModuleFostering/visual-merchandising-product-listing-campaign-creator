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

        $setup_status = Mage::getModel('tagalys_core/config')->getTagalysConfig('setup_status');
        if ($setup_status == 'completed') {
            return $this->__('Configuration');
        } else {
            return $this->__('Setup');
        }
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
