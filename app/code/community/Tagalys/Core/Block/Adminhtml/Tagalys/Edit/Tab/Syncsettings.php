<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Syncsettings extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {
    
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

        $form->setHtmlIdPrefix('tagalys_admin_core_');
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        
        $fieldset = $form->addFieldset('tagalys_sync_fieldset', array('legend' => $this->__('Sync Settings')));

        $fieldset->addField('stores_for_tagalys', 'multiselect', array(
            'label'     => $this->__('Choose stores for which you want to enable Tagalys features'),
            // 'class'     => 'required-entry',
            // 'required'  => true,
            'name'      => 'stores_for_tagalys',
            'style' => "width:100%; height: 125px;",
            'onclick' => "return false;",
            'onchange' => "return false;",
            'value'  => $this->_helper->getStoresForTagalys(),
            'values' => $this->_helper->getAllWebsiteStores(),
            'disabled' => false,
            'readonly' => false,
            'after_element_html' => '<small>Products and configuration for these stores will be synced to Tagalys</small>',
            'tabindex' => 1
        ));
        
        $setup_status = Mage::getModel('tagalys_core/config')->getTagalysConfig('setup_status');
        if ($setup_status == 'complete') {
        } else {
            $fieldset->addField('agree_cron_enabled', 'checkbox', array(
                'name'      => 'Checkbox',
                'checked' => false,
                'onclick'   => 'this.value = this.checked ? 1 : 0;',
                'disabled' => false,
                'after_element_html' => '<small>Cron is enabled on this Magento installation.<br><em><b><a target=_blank href="http://devdocs.magento.com/guides/m1x/install/installing_install.html#install-cron">Cron Documentation</a></b></em></small>',
                'tabindex' => 1
            ));

            $fieldset->addField('agree_start_sync', 'checkbox', array(
                'name'      => 'Checkbox',
                'checked' => false,
                'onclick'   => 'this.value = this.checked ? 1 : 0;',
                'disabled' => false,
                'after_element_html' => '<small>I agree to start syncing the selected stores.<br><em><b>We recommend you to do this at low traffic hours.</b></em></small>',
                'tabindex' => 1
            ));

            $fieldset->addField('note_checkboxes', 'note', array(
                'text' => $this->__('<em>Please check both checkboxes to continue.</em>'),
            ));

            $fieldset->addField('submit', 'submit', array(
                'name' => 'tagalys_submit_action',
                'value' => 'Save & Start Sync',
                'class'=> "tagalys-btn",
                'disabled' => true,
                'style'   => "width:100%",
                'onclick' => 'if(this.classList.contains(\'clicked\')) { return false; } else {  this.className += \' clicked\'; var that = this; setTimeout(function(){ that.value=\'Please wait…\'; that.disabled=true; }, 50); return true; }',
                'after_element_html' => '<small><em></em></small>',
                'tabindex' => 1
            ));

        }

        $this->setForm($form);
        return parent::_prepareForm();
    }


    public function getTabLabel() {
        return $this->__('Sync Settings');
    }

        /**
     * Tab title getter
     *
     * @return string
     */
        public function getTabTitle() {
            return $this->__('Sync Settings');
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