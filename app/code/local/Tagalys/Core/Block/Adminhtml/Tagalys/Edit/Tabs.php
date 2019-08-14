<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
  /**
   * Setting tab id, DOM destination element id, title
   */
  public function __construct() {
    parent::__construct();
    $this->setId('tagalys');
    $this->setDestElementId('edit_form');
    $this->setTitle($this->__('Tagalys'));
    $this->_helper = Mage::helper('tagalys_core');

  }
  protected function _beforeToHtml()
  { 
    $status = Mage::helper('tagalys_core')->getTagalysConfig('setup_complete') && $this->_helper->getTagalysConfig("is_tagalys_active");
    if((int)$this->_helper->getTagalysConfig("is_signup")) {
     $this->setActiveTab('core');
     if((int)$this->_helper->getTagalysConfig("is_tagalys_active")) {
       $this->setActiveTab('setup');
       $stores_setup = $this->_helper->getTagalysConfig("stores_setup");
       if(!empty($stores_setup)) {
        $this->setActiveTab('sync');
      }
    } 
  } else {
    $this->setActiveTab('general');
  }
  if($status && Mage::helper('core')->isModuleEnabled('Tagalys_SearchSuggestions')) {
    $this->setActiveTab('tagalys_tsearch_ss');
  }
  if($status && Mage::helper('core')->isModuleEnabled('Tagalys_Tsearch')) {
    $this->setActiveTab('tagalys_tsearch');
  }
  if($status && Mage::helper('core')->isModuleEnabled('Tagalys_MerchandisingPage')) {
    $this->setActiveTab('tagalys_merchandisingpage');
  }
  if($status && Mage::helper('core')->isModuleEnabled('Tagalys_SimilarProducts')) {
    $this->setActiveTab('tagalys_similarproducts');
  }
  return parent::_beforeToHtml();
}
  /**
   * Preparing global layout
   *
   * @return Mage_Core_Block_Abstract
   */
  protected function _prepareLayout() {
    $status = Mage::helper('tagalys_core')->getTagalysConfig('setup_complete') && $this->_helper->getTagalysConfig("search_complete");
    $this->addTab('general', array(
                  'label'     => $status ? $this->__('Account') : $this->__('Step 1 : Signup'),
                  'content'   => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_signup')
                  ->toHtml(),
                  ));

    if((int)$this->_helper->getTagalysConfig("is_signup")) {
      $this->addTab('core', array(
                    'label'     => $status ? $this->__('Credentials') : $this->__('Step 2 : Credentials'),
                    'content'   => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_credentials')
                    ->toHtml(),
                    ));

      if((int)$this->_helper->getTagalysConfig("is_tagalys_active")) { //to-do
        $this->addTab('setup', array(
                      'label'     => $status ? $this->__('Catalog Sync Settings') : $this->__('Step 3 : Initial Settings'),
                      'content'   => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_setup')
                      ->toHtml(),
                      ));  
        $stores_setup = $this->_helper->getTagalysConfig("stores_setup");
        if(!empty($stores_setup)) {
          $this->addTab('sync', array(
                        'label'     => $status ? $this->__('Catalog Sync Status') : $this->__('Step 4 : Catalog Sync Status'),
                        'content'   => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_sync')->toHtml(),

                        ));  
        }
      }
    }
    if($status && Mage::helper('core')->isModuleEnabled('Tagalys_SearchSuggestions')) {
      $this->addTab('tagalys_tsearch_ss', array(
                    'label'     => $this->__('Search Suggestions Settings'),
                    'content'   => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_tsearchsuggestion')
                    ->toHtml()
                    ));
    }
    if($status && Mage::helper('core')->isModuleEnabled('Tagalys_Tsearch')) {
      $this->addTab('tagalys_tsearch', array(
                    'label'     => $this->__('Site Search Settings'),
                    'content'   => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_tsearch')
                    ->toHtml()
                    ));
    }
    if($status && Mage::helper('core')->isModuleEnabled('Tagalys_MerchandisingPage')) {
      $this->addTab('tagalys_merchandisingpage', array(
                    'label'     => $this->__('Merchandising Page Settings'),
                    'content'   => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_merchandisingpage')
                    ->toHtml()
                    ));
    }

    if($status && Mage::helper('core')->isModuleEnabled('Tagalys_SimilarProducts')) {
      $this->addTab('tagalys_similarproducts', array(
                    'label'     => $this->__('Similar Products Settings'),
                    'content'   => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_similarproducts')
                    ->toHtml()
                    ));
    }
    
    if($stores_setup) {
      $this->addTab('tagalys_debug', array(
                    'label'     => $this->__('Troubleshooting'),
                    'content'   => $this->getLayout()->createBlock('tagalys_core/adminhtml_tagalys_edit_tab_debug')
                    ->toHtml()
                    ));
    }

    return parent::_prepareLayout();
  }
}
