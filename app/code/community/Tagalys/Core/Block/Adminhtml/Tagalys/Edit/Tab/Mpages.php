<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Mpages extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function __construct() {
        parent::__construct();
    }

    protected function _prepareForm() {
        $this->_helper = Mage::helper('tagalys_core');

        $form = Mage::getModel('varien/data_form', array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/tagalys', array('_current'  => true)),
            'method'  => 'post'
        ));

        $form->setHtmlIdPrefix('admin_tagalys_core_');
        $htmlIdPrefix = $form->getHtmlIdPrefix();

        $fieldset = $form->addFieldset('tagalys_mpages_fieldset', array('legend' => $this->__('Merchandising Pages')));

        $fieldset->addField('enable_mpages', 'select', array(
            'name' => 'enable_mpages',
            'label' => 'Enable',
            'title' => 'Enable',
            'options' => array(
                '0' => $this->__('No'),
                '1' => $this->__('Yes'),
            ),
            'required' => true,
            'style' => 'width:100%',
            'value' => Mage::getModel('tagalys_core/config')->getTagalysConfig("module:mpages:enabled")
        ));

        $fieldset->addField('submit', 'submit', array(
            'name' => 'tagalys_submit_action',
            'value' => 'Save Merchandising Pages Settings',
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
        return $this->__('Merchandising Pages');
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->__('Merchandising Pages');
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