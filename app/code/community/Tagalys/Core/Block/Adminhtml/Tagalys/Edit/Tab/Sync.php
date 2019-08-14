<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Sync extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function __construct() {
        parent::__construct();
    }

    protected function _prepareForm() {
        $form = Mage::getModel('varien/data_form', array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/tagalys', array('_current'  => true)),
            'method'  => 'post'
        ));

        $form->setHtmlIdPrefix('admin_tagalys_core_');
        $htmlIdPrefix = $form->getHtmlIdPrefix();

        $setup_status = Mage::getModel('tagalys_core/config')->getTagalysConfig('setup_status');
        $setup_complete = ($setup_status == 'completed');
        if ($setup_complete == false) {
            $init_email = $form->addFieldset('tagalys_email_fieldset', array(
                'style'   => "width:100%",
            ));
            $init_email->addField('email_note', 'note', array(
                'after_element_html' =>'<b>If you have Cron enabled, sync is automatic. If not, please use the manual sync option and keep this browser window open.<br><br>Once all stores are synced, Tagalys features will be enabled automatically.<br><br>If you have any issues, please <a href="mailto:cs@tagalys.com">email us</a></b>' 
            ));
        }

        $sync_control_fieldset = $form->addFieldset("sync_control_fieldset", array('legend' => 'Sync'));
        $sync_control_fieldset->addField("sync_status_note", 'note', array(
            'label' => 'Status',
            'text' => '<span id="note_sync_status"></span>'
        ));
        $sync_control_fieldset->addField("sync_control_manual_note", 'note', array(
            'label' => 'Sync Manually',
            'text' => '<strong>You\'ll have to keep this browser window open and stay connected to the Internet for manual sync to work.</strong>'
        ));
        $sync_control_fieldset->addField("sync_control_manual", 'note', array(
            'text' => '<a id="tagalys-toggle-manual-sync" href="#" target="_blank" class="tagalys-btn" onclick="tagalysToggleManualSync(); return false;">Sync Now</a>'
        ));
        $sync_control_fieldset->addField("sync_control_auto_note", 'note', array(
            'label' => 'Sync via Cron',
            'text' => 'If you have Cron enabled, sync is automatic.'
        ));


        foreach (Mage::helper('tagalys_core')->getStoresForTagalys() as $key => $store_id) {
            $store_sync_fieldset = $form->addFieldset("store_{$store_id}_fieldset", array('legend' => $this->__("Store: " . Mage::getModel('core/store')->load($store_id)->getName())));
            $store_sync_fieldset->addField("store_{$store_id}_note_setup_complete", 'note', array(
                'label' => 'Setup complete',
                'text' => '<span id="store_'.$store_id.'_note_setup_complete"></span>'
            ));
            $store_sync_fieldset->addField("store_{$store_id}_note_feed_status", 'note', array(
                'label' => 'Feed Status',
                'text' => '<span id="store_'.$store_id.'_note_feed_status"></span>'
            ));
            $store_sync_fieldset->addField("store_{$store_id}_note_updates_status", 'note', array(
                'label' => 'Updates Status',
                'text' => '<span id="store_'.$store_id.'_note_updates_status"></span>'
            ));
        }

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