<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Debug extends Mage_Adminhtml_Block_Widget_Form
implements Mage_Adminhtml_Block_Widget_Tab_Interface {

  public function __construct() {
    parent::__construct();
  }


  protected function _prepareForm() {

    /** @var $form Varien_Data_Form */
    $form = Mage::getModel('varien/data_form', array(
                           'id' => 'edit_form',
                           'action' => $this->getUrl('*/tagalys', array('_current'  => true)),
                           'method'  => 'post'
                           ));

    $form->setHtmlIdPrefix('admin_');
    $htmlIdPrefix = $form->getHtmlIdPrefix();


    $debug_fieldset = $form->addFieldset('debug_fieldset', array('legend' => $this->__('Manual Product Resync')));


    $debug_fieldset->addField('note_resync', 'note', array(
                              'text'     => $this->__('Do not manually sync the catalog unless you face any issues with normal setup.  This is not a part of regular setup, please do this with guidance from the tagalys team (cs@tagalys.com)'),
                              ));

    $debug_fieldset->addField('submit_resync', 'submit', array(
                              'name' => 'submit_resync',
                              'value' => 'Manual Catalog Resync',
                              'class'=> "tagalys-btn",
                              'tabindex' => 1
                              ));

    $debug_fieldset = $form->addFieldset('debug_fieldset1', array('legend' => $this->__('Manual Configuration Resync')));


    $debug_fieldset->addField('note_reconfig', 'note', array(
                              'text'     => $this->__('Do not manually sync the Configuration unless you face any issues with normal setup.  This is not a part of regular setup, please do this with guidance from the tagalys team (cs@tagalys.com)'),
                              ));

    $debug_fieldset->addField('submit_reconfig', 'submit', array(
                              'name' => 'submit_reconfig',
                              'value' => 'Manual Configuration Update',
                              'class'=> "tagalys-btn",
                              'tabindex' => 1
                              ));

    $this->setForm($form);
    return parent::_prepareForm();
  }


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
    return true;
  }

  /**
   * Check if tab hidden
   *
   * @return bool
   */
  public function isHidden() {
    return false;
  }
}