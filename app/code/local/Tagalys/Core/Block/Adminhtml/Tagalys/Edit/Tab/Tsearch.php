<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Tsearch extends Mage_Adminhtml_Block_Widget_Form
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

    $form->setHtmlIdPrefix('admin_tagalys_tsearch');
    $htmlIdPrefix = $form->getHtmlIdPrefix();
    $fieldset = $form->addFieldset('tagalys_tsearch_fieldset', array('legend' => $this->__('Site Search Settings')));

   
    //to-do show only after 100%
    $fieldset->addField('is_tsearch_active', 'select', array(
      'name'      => 'is_tsearch_active',
      'label'     => $this->__('Enable search'),
      'title'     => $this->__('Enable search'),
      'options'   => array(
        '0' => $this->__('No'),
        '1' => $this->__('Yes'),
        ),
      'required'  => true,
      'style'   => "width:100%",
      'value'     => (int)$this->_helper->getTagalysConfig("is_tsearch_active")
      ));


    $fieldset->addField('submit', 'submit', array(
      'name' => 'submit_search_config',
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