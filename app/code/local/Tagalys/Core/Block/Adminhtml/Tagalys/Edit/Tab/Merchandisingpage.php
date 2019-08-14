<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Merchandisingpage extends Mage_Adminhtml_Block_Widget_Form
implements Mage_Adminhtml_Block_Widget_Tab_Interface {

 public function __construct() {
    parent::__construct();
  }

 protected function _prepareForm() {
    $this->_helper = Mage::helper('tagalys_core');

   /** @var $form Varien_Data_Form */
    $form = Mage::getModel('varien/data_form', array(
      'id' => 'tsearch_edit_form',
      'action' => $this->getUrl('*/tagalys', array('_current'  => true)),
      'method'  => 'post'
      ));

   $form->setHtmlIdPrefix('admin_tagalys_merchandisingpage');
    $htmlIdPrefix = $form->getHtmlIdPrefix();
    $fieldset = $form->addFieldset('tagalys_merchandisingpage_fieldset', array('legend' => $this->__('Merchandising Page Settings')));

   $fieldset->addField('is_merchandising_page_active', 'select', array(
      'name'      => 'is_merchandising_page_active',
      'label'     => $this->__('Enable merchandising page'),
      'title'     => $this->__('Enable merchandising page'),
      'options'   => array(
        '0' => $this->__('No'),
        '1' => $this->__('Yes'),
        ),
      'required'  => true,
      'style'   => "width:100%",
      'value'     => (int)$this->_helper->getTagalysConfig("is_merchandising_page_active")
      ));

 
    $fieldset->addField('merchandising_page_template', 'textarea', array(
      'label'     => $this->__('Enter Template Config'),
      'required'  => false,
      'name'      => 'merchandising_page_template',
      'value'  => $this->_helper->getTagalysConfig("merchandising_page_template"),
      'disabled' => false,
      // 'readonly' => false,
      'style'   => "width:100%",
      'after_element_html' => '<small>  </small>',
      'tabindex' => 1
      ));

   $fieldset->addField('submit', 'submit', array(
      'name' => 'submit_merchandising_page_config',
      'value' => 'Submit',
      'class'=> "tagalys-btn",
      'tabindex' => 1
      ));

   $this->setForm($form);
    return parent::_prepareForm();
  }

 /**
   * Tab label getter
   *
   * @return string
   */
  public function getTabLabel() {
    return $this->__('Settings');
  }

 /**
   * Tab title getter
   *
   * @return string
   */
  public function getTabTitle() {
    return $this->__('Settings');
  }

 /**
   * Check if tab can be shown
   *
   * @return bool
   */
  public function canShowTab() {
    return false;
  }

 /**
   * Check if tab hidden
   *
   * @return bool
   */
  public function isHidden() {
    return true;
  }
}