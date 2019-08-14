<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Tsearchsuggestion extends Mage_Adminhtml_Block_Widget_Form
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

    $form->setHtmlIdPrefix('admin_tagalys_tsearch_ss');
    $htmlIdPrefix = $form->getHtmlIdPrefix();
    $fieldset = $form->addFieldset('tagalys_tsearch_ss_fieldset', array('legend' => $this->__('Search Suggestion Settings')));

     $fieldset->addField('is_tsearchsuggestion_active', 'select', array(
      'name'      => 'is_tsearchsuggestion_active',
      'label'     => $this->__('Enable Search Suggestions'),
      'title'     => $this->__('Enable Search Suggestions'),
      'options'   => array(
        '0' => $this->__('No'),
        '1' => $this->__('Yes'),
        ),
      'required'  => true,
      'style'   => "width:100%",
      'value'     => (int)$this->_helper->getTagalysConfig("is_tsearchsuggestion_active")
      ));

    $fieldset->addField('search_box', 'text', array(
      'label'     => $this->__('Enter Search Box Selector'),
      'required'  => false,
      'name'      => 'search_box',
      'value'  => $this->_helper->getTagalysConfig("search_box"),
      'disabled' => false,
      // 'readonly' => false,
      'style'   => "width:100%",
      'after_element_html' => '<small> Please consult with your tech team or <a href="mailto:cs@tagalys.com">contact us</a>. <br> This can either be an ID or a Class Selector.<br> Eg: #search or .search-field </small>',
      'tabindex' => 1
      ));


    $fieldset->addField('search_box_container', 'text', array(
      'label'     => $this->__('Enter Search Box Container'),
      'required'  => false,
      'name'      => 'search_box_container',
      'value'  => $this->_helper->getTagalysConfig("search_box_container"),
      'disabled' => false,
      // 'readonly' => false,
      'style'   => "width:100%",
      'after_element_html' => '<em>This is the element to which you want Tagalys suggestions box to be aligned with. This can either be the search input box itself or any container around it based on your design. If left blank, it will be aligned to the default search input box.</em><br><small> Please consult with your tech team or <a href="mailto:cs@tagalys.com">contact us</a>. <br> This can either be an ID or a Class Selector.<br> Eg: #search or .search-field </small>',
      'tabindex' => 1
      ));


    $fieldset->addField('submit', 'submit', array(
      'name' => 'submit_search_config',
      'value' => 'Submit',
      'class'=> "tagalys-btn",
      'tabindex' => 1
      ));

	    $fieldset->addField('ss_note', 'note', array(
        'after_element_html' =>'<b> You need to disable Magento default search auto complete/suggestions. Please refer <a target="_blank" href="http://stackoverflow.com/questions/8688338/remove-magentos-search-suggest-feature">Link</a></b>' 
        ));

      $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
      ->addFieldMap("{$htmlIdPrefix}is_tsearchsuggestion_active", 'is_tsearchsuggestion_active')
      ->addFieldMap("{$htmlIdPrefix}search_box", 'search_box')
      ->addFieldMap("{$htmlIdPrefix}search_box_container", 'search_box_container')
      ->addFieldDependence('search_box', 'is_tsearchsuggestion_active', '1')
      ->addFieldDependence('search_box_container', 'is_tsearchsuggestion_active', '1')
      );

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