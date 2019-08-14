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
                'after_element_html' =>'<b>You can close this screen now. Once setup is complete for all selected stores, you will be notified by email and Tagalys features will be enabled automatically.<br><br>If you have any issues, please <a href="mailto:cs@tagalys.com">email us</a></b>' 
            ));
        }

        foreach (Mage::helper('tagalys_core')->getStoresForTagalys() as $key => $store_id) {
            $store_sync_fieldset = $form->addFieldset("store_{$store_id}_fieldset", array('legend' => $this->__("Store: " . Mage::getModel('core/store')->load($store_id)->getName())));
            $store_setup_complete = Mage::getModel('tagalys_core/config')->getTagalysConfig("store:$store_id:setup_complete");
            $store_sync_fieldset->addField("store_{$store_id}_setup_complete", 'note', array(
                'label' => 'Setup complete',
                'text' => (($store_setup_complete == '1') ? 'Yes' : 'No')
            ));
            $store_feed_status = Mage::getModel('tagalys_core/config')->getTagalysConfig("store:$store_id:feed_status", true);
            if ($store_feed_status != null) {
                $status_for_client = '';
                switch($store_feed_status['status']) {
                    case 'pending':
                        $status_for_client = 'Waiting for Cron';
                        break;
                    case 'processing':
                        $status_for_client = 'Processing';
                        break;
                    case 'generated_file':
                        $status_for_client = 'Generated file. Sending to Tagalys.';
                        break;
                    case 'sent_to_tagalys':
                        $status_for_client = 'Waiting for Tagalys';
                        break;
                    case 'finished':
                        $status_for_client = 'Finished';
                        break;
                }
                $store_resync_required = Mage::getModel('tagalys_core/config')->getTagalysConfig("store:$store_id:resync_required");
                if ($store_resync_required == '1' && $status_for_client == 'Finished') {
                    $status_for_client = 'Scheduled';
                }
                if ($status_for_client == 'Waiting for Cron' && $store_feed_status['completed_count'] > 0) {
                    $status_for_client = 'In progress. Waiting for Cron.';
                }
                $store_sync_fieldset->addField("store_{$store_id}_feed_status", 'note', array(
                    'label' => 'Feed status',
                    'text' => $status_for_client
                ));
                $updated_at = new DateTime((string)$store_feed_status['updated_at']);
                $updated_at->setTimeZone(new DateTimeZone(Mage::getStoreConfig('general/locale/timezone')));
                $store_sync_fieldset->addField("store_{$store_id}_feed_updated_at", 'note', array(
                    'label' => 'Feed status last changed at',
                    'text' => $updated_at->format("F j, Y, g:i a")
                ));
            }
            if ($store_setup_complete == '1') {
                $store_updates_status = Mage::getModel('tagalys_core/config')->getTagalysConfig("store:$store_id:updates_status", true);
                $remaining_updates = Mage::getModel('tagalys_core/queue')->getCollection()->count();
                if ($store_updates_status != null) {
                    $status_for_client = '';
                    switch($store_updates_status['status']) {
                        case 'pending':
                            $status_for_client = 'Waiting for Cron';
                            break;
                        case 'processing':
                            $status_for_client = 'Processing';
                            break;
                        case 'generated_file':
                            $status_for_client = 'Generated file. Sending to Tagalys.';
                            break;
                        case 'sent_to_tagalys':
                            $status_for_client = 'Waiting for Tagalys';
                            break;
                        case 'finished':
                            $status_for_client = 'Finished';
                            break;
                    }
                    if (in_array($status_for_client, array('Finished')) && $remaining_updates > 0) {
                        $status_for_client = 'Waiting for Cron';
                    }
                    $store_sync_fieldset->addField("store_{$store_id}_updates_status", 'note', array(
                        'label' => 'Updates status',
                        'text' => $status_for_client
                    ));
                    $updated_at = new DateTime((string)$store_updates_status['updated_at']);
                    $updated_at->setTimeZone(new DateTimeZone(Mage::getStoreConfig('general/locale/timezone')));
                    $store_sync_fieldset->addField("store_{$store_id}_updates_updated_at", 'note', array(
                        'label' => 'Updates status last changed at',
                        'text' => $updated_at->format("F j, Y, g:i a")
                    ));
                } else {
                    if ($remaining_updates == 0) {
                        $status_for_client = 'Finished';
                    } else {
                        $status_for_client = 'Waiting for Cron';
                    }
                    $store_sync_fieldset->addField("store_{$store_id}_updates_status", 'note', array(
                        'label' => 'Updates Status',
                        'text' => $status_for_client
                    ));
                }
            }
            $store_sync_fieldset->addField("store_{$store_id}_refresh_status", 'note', array(
                'text' => '<a href="#" class="tagalys-btn" onclick="location.reload(); return false;">Refresh Status</a>'
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