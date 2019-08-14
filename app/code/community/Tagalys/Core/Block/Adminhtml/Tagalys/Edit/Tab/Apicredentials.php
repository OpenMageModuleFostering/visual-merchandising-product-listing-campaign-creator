<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Apicredentials extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {

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

        $tagalys_dashboard_fieldset = $form->addFieldset('tagalys_dashboard_fieldset', array('legend' => $this->__('Tagalys Dashboard')));

        $tagalys_dashboard_fieldset->addField('note', 'note', array(
            'label' => $this->__('Your Tagalys account'),
            'text' => '<img src='. $this->getSkinUrl("images/tagalys/tagalys-logo.png") .' alt="" width="125" />'.'<br>',
        ));
        $setup_status = Mage::getModel('tagalys_core/config')->getTagalysConfig('setup_status');
        if ($setup_status == 'api_credentials') {
            $tagalys_dashboard_fieldset->addField('note_dashboard', 'note', array(
                'text' => '<a href="https://next.tagalys.com/signup" target="_blank" class="tagalys-btn">Sign up for a Tagalys account</a>'
            ));
        } else {
            $tagalys_dashboard_fieldset->addField('note_dashboard', 'note', array(
                'text' => '<a href="https://next.tagalys.com" target="_blank" class="tagalys-btn">Access your Tagalys Dashboard</a>'
            ));
        }

        $fieldset = $form->addFieldset('tagalys_core_fieldset', array('legend' => $this->__('API Credentials')));

        $fieldset->addField('api_credentials', 'textarea', array(
            'name' => 'api_credentials',
            'label' => $this->__('Paste API Credentials'),
            'required' => true,
            'value' => Mage::getModel('tagalys_core/config')->getTagalysConfig('api_credentials'),
            'style' => "width:100%; height: 100px;",
            'after_element_html' => '<small>You can find your API Credentials in the Integration section of your <a href="https://next.tagalys.com" target="_blank">Tagalys Dashboard</a></small>',
            'tabindex' => 1
        ));

        $fieldset->addField('submit', 'submit', array(
            'name' => 'tagalys_submit_action',
            'value' => 'Save API Credentials',
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
        return $this->__('API Credentials');
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->__('API Credentials');
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