<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Support extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {

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

        $tagalys_support_fieldset = $form->addFieldset('tagalys_support_fieldset', array('legend' => $this->__('Support')));

        $tagalys_support_fieldset->addField('support_email', 'note', array(
            'label' => $this->__('Email'),
            'text' => '<a href="mailto:cs@tagalys.com">cs@tagalys.com</a>',
        ));

        $tagalys_support_fieldset->addField('support_home', 'note', array(
            'label' => $this->__('Documentation & FAQs'),
            'text' => '<a href="http://support.tagalys.com" target="_blank">http://support.tagalys.com</a>',
        ));

        $tagalys_support_fieldset->addField('support_ticket', 'note', array(
            'label' => $this->__('Support Tickets'),
            'text' => '<a href="http://support.tagalys.com/support/tickets/new" target="_blank">Submit a new Ticket</a><br><a href="http://support.tagalys.com/support/tickets" target="_blank">Check status</a>',
        ));

        $troubleshooting_info_fieldset = $form->addFieldset("troubleshooting_info_fieldset", array('legend' => $this->__("Troubleshooting Info")));

        $info = array('config' => array(), 'files_in_media_folder' => array());
        $config_collection = Mage::getResourceModel('tagalys_core/config_collection');
        foreach($config_collection as $i) {
            $info['config'][$i->getData('path')] = $i->getData('value');
        }
        $media_directory = Mage::getBaseDir('media'). DS .'tagalys';
        $files_in_media_directory = scandir($media_directory);
        foreach ($files_in_media_directory as $key => $value) {
            if (!is_dir($media_directory . DS . $value)) {
                if (!preg_match("/^\./", $value)) {
                    $info['files_in_media_folder'][] = $value;
                }
            }
        }

        $troubleshooting_info_fieldset->addField('troubleshooting_info', 'textarea', array(
            'name' => 'troubleshooting_info',
            'label' => $this->__('Troubleshooting Info'),
            'readonly' => true,
            'value' => json_encode($info),
            'style' => "width:100%; height: 100px;",
            'after_element_html' => 'Please copy and send the above content to <a href="mailto:cs@tagalys.com">cs@tagalys.com</a> to help us troubleshoot issues.',
            'tabindex' => 1
        ));

        $setup_status = Mage::getModel('tagalys_core/config')->getTagalysConfig('setup_status');
        if (in_array($setup_status, array('sync', 'completed'))) {
            $tagalys_full_resync_fieldset = $form->addFieldset('tagalys_full_resync_fieldset', array('legend' => $this->__('Full Products Resync')));

            $tagalys_full_resync_fieldset->addField('note_resync', 'note', array(
                'text' => $this->__('This will trigger a full resync of your products to Tagalys. Do this only with direction from Tagalys Support. Please note that this will cause high CPU usage on your server. We recommend that you do this at low traffic hours.')
            ));

            $tagalys_full_resync_fieldset->addField('submit_resync', 'submit', array(
                'label' => '',
                'name' => 'tagalys_submit_action',
                'value' => 'Trigger full products resync now',
                'onclick' => 'if (this.classList.contains(\'clicked\')) { return false; } else {  this.className += \' clicked\'; var that = this; setTimeout(function(){ that.value=\'Please wait…\'; that.disabled=true; }, 50); return true; }',
                'class'=> "tagalys-btn",
                'tabindex' => 1
            ));

            $tagalys_full_resync_fieldset->addField('note_cron', 'note', array(
                'text' => $this->__('<small><b>NOTE: Please make sure Cron is setup and running. <a target=_blank href="http://devdocs.magento.com/guides/m1x/install/installing_install.html#install-cron">Cron Documentation</a></b></small>'),
            ));

            $tagalys_sync_config_fieldset = $form->addFieldset('tagalys_sync_config_fieldset', array('legend' => $this->__('Configuration Resync')));

            $tagalys_sync_config_fieldset->addField('note_resync_config', 'note', array(
                'text' => $this->__('This will trigger a resync of your configuration to Tagalys. Do this only with direction from Tagalys Support.')
            ));

            $tagalys_sync_config_fieldset->addField('submit_resync_config', 'submit', array(
                'label' => '',
                'name' => 'tagalys_submit_action',
                'value' => 'Trigger configuration resync now',
                'onclick' => 'if (this.classList.contains(\'clicked\')) { return false; } else {  this.className += \' clicked\'; var that = this; setTimeout(function(){ that.value=\'Please wait…\'; that.disabled=true; }, 50); return true; }',
                'class'=> "tagalys-btn",
                'tabindex' => 1
            ));
        }

        $tagalys_restart_setup_fieldset = $form->addFieldset('tagalys_restart_setup_fieldset', array('legend' => $this->__('Restart Tagalys Setup')));

        $tagalys_restart_setup_fieldset->addField('note_restart_setup', 'note', array(
            'text' => $this->__('<span class="error"><b>Caution:</b> This will disable Tagalys features and remove all Tagalys configuration from your Magento installation. To continue using Tagalys, you\'ll have to configure and sync products again. There is no undo.</span>')
        ));

        $tagalys_restart_setup_fieldset->addField('submit_restart_setup', 'submit', array(
            'label' => '',
            'name' => 'tagalys_submit_action',
            'value' => 'Restart Tagalys Setup',
            'onclick' => 'if (confirm(\'Are you sure? This will disable Tagalys from your installation and you will have to start over. There is no undo.\')) { if (this.classList.contains(\'clicked\')) { return false; } else {  this.className += \' clicked\'; var that = this; setTimeout(function(){ that.value=\'Please wait…\'; that.disabled=true; }, 50); return true; } } else { return false; }',
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
        return $this->__('Support & Troubleshooting');
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->__('Support & Troubleshooting');
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