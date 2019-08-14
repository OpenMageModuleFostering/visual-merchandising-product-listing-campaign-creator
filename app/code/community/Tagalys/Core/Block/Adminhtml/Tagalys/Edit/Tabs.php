<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
    public function __construct() {
        parent::__construct();
        $this->setId('tagalys');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Tagalys'));
        $this->_helper = Mage::helper('tagalys_core');
    }
    protected function _beforeToHtml() {
        $setup_status = Mage::getModel('tagalys_core/config')->getTagalysConfig('setup_status');
        /*
            api_credentials
            sync_settings
            sync
            completed
        */
        if ($setup_status != 'completed') {
            // go to current status tab
            $this->setActiveTab($setup_status);
        } else {
            $tab_param = Mage::app()->getRequest()->getParam('tab', null);
            if ($tab_param != null) {
                $this->setActiveTab($tab_param);
            }
        }

        return parent::_beforeToHtml();
    }
    /**
     * Preparing global layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout() {
        $setup_status = Mage::getModel('tagalys_core/config')->getTagalysConfig('setup_status');
        $setup_complete = ($setup_status == 'completed');
        $this->addTab('api_credentials', array(
            'label' => $setup_complete ? $this->__('Dashboard & API Credentials') : $this->__('Step 1: API Credentials'),
            'content' => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_apicredentials')->toHtml(),
        ));

        if (in_array($setup_status, array('sync_settings', 'sync', 'completed'))) {
            $this->addTab('sync_settings', array(
                'label' => $setup_complete ? $this->__('Sync Settings') : $this->__('Step 2: Sync Settings'),
                'content' => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_syncsettings')->toHtml(),
            ));
        }

        if (in_array($setup_status, array('sync', 'completed'))) {
            $this->addTab('sync', array(
                'label' => $setup_complete ? $this->__('Sync Status') : $this->__('Step 3: Sync Status'),
                'content' => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_sync')->toHtml(),
            ));
        }

        if ($setup_status == 'completed') {
            $this->addTab('search_suggestions', array(
                'label' => 'Search Suggestions',
                'content' => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_searchsuggestions')->toHtml(),
            ));
            $this->addTab('search', array(
              'label' => 'Search',
              'content' => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_search')->toHtml(),
            ));
            $this->addTab('mpages', array(
              'label' => 'Merchandising Pages',
              'content' => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_mpages')->toHtml(),
            ));
        }

        $this->addTab('support', array(
            'label' => $this->__('Support & Troubleshooting'),
            'content' => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_support')->toHtml(),
        ));
    }
}
