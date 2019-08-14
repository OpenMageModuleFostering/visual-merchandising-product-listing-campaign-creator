<?php

class Tagalys_Core_Adminhtml_TagalysController extends Mage_Adminhtml_Controller_Action {

    protected function _isAllowed() {
        return true;
    }

    public function indexAction() {
        $this->_title('Tagalys Configuration');
        // $this->_getSession()->addNotice($this->__('Need help? Visit <a href="http://support.tagalys.com" target="_blank">http://support.tagalys.com</a>'));
        $this->loadLayout();
        $this->_setActiveMenu('Tagalys/core');
        $this->renderLayout();
    }

    public function saveAction() {
        $params = $this->getRequest()->getParams();
        if (!empty($params['tagalys_submit_action'])) {
            $result = false;
            $this->_helper = Mage::helper('tagalys_core');
            $redirect_to_tab = null;
            switch ($params['tagalys_submit_action']) {
                case 'Save API Credentials':
                    try {
                        $result = $this->_saveApiCredentials($params);
                        if ($result !== false) {
                            Mage::getSingleton('tagalys_core/client')->log('info', 'Saved API credentials', array('api_credentials' => $params['api_credentials']));
                            $setup_status = Mage::getModel('tagalys_core/config')->getTagalysConfig('setup_status');
                            if ($setup_status == 'api_credentials') {
                                Mage::getModel('tagalys_core/config')->setTagalysConfig('setup_status', 'sync_settings');
                            }
                        }
                        $redirect_to_tab = 'api_credentials';
                    } catch (Exception $e) {
                        Mage::getSingleton('tagalys_core/client')->log('error', 'Error in _saveApiCredentials: ' . $e->getMessage(), array('api_credentials' => $params['api_credentials']));
                        Mage::getSingleton('core/session')->addError("Sorry, something went wrong while saving your API credentials. Please <a href=\"mailto:cs@tagalys.com\">email us</a> so we can resolve this issue.");
                        $redirect_to_tab = 'api_credentials';
                    }
                    break;
                case 'Save & Continue to Sync':
                    try {
                        if (count($params['stores_for_tagalys']) > 0) {
                            Mage::getSingleton('tagalys_core/client')->log('info', 'Starting configuration sync', array('stores_for_tagalys' => $params['stores_for_tagalys']));
                            $result = Mage::helper("tagalys_core/service")->syncClientConfiguration($params['stores_for_tagalys']);
                            if ($result === false) {
                                Mage::getSingleton('tagalys_core/client')->log('error', 'syncClientConfiguration returned false', array('stores_for_tagalys' => $params['stores_for_tagalys']));
                                Mage::getSingleton('core/session')->addError("Sorry, something went wrong while saving your store's configuration. We've logged the issue and we'll get back once we know more. You can contact us here: <a href=\"mailto:cs@tagalys.com\">cs@tagalys.com</a>");
                                $redirect_to_tab = 'sync_settings';
                            } else {
                                Mage::getSingleton('tagalys_core/client')->log('info', 'Completed configuration sync', array('stores_for_tagalys' => $params['stores_for_tagalys']));
                                Mage::getModel('tagalys_core/config')->setTagalysConfig('stores', json_encode($params['stores_for_tagalys']));
                                foreach($params['stores_for_tagalys'] as $i => $store_id) {
                                    Mage::helper("tagalys_core/SyncFile")->triggerFeedForStore($store_id);
                                }
                                $setup_status = Mage::getModel('tagalys_core/config')->getTagalysConfig('setup_status');
                                if ($setup_status == 'sync_settings') {
                                    Mage::getModel('tagalys_core/config')->setTagalysConfig('setup_status', 'sync');
                                }
                                $redirect_to_tab = 'sync';
                            }
                        } else {
                            Mage::getSingleton('core/session')->addError("Please choose at least one store to continue.");
                            $redirect_to_tab = 'sync_settings';
                        }
                    } catch (Exception $e) {
                        Mage::getSingleton('tagalys_core/client')->log('error', 'Error in syncClientConfiguration: ' . $e->getMessage(), array('stores_for_tagalys' => $params['stores_for_tagalys']));
                        Mage::getSingleton('core/session')->addError("Sorry, something went wrong while saving your configuration. Please <a href=\"mailto:cs@tagalys.com\">email us</a> so we can resolve this issue.");
                        $redirect_to_tab = 'sync_settings';
                    }
                    break;
                case 'Save Search Suggestions Settings':
                    Mage::getModel('tagalys_core/config')->setTagalysConfig('module:search_suggestions:enabled', $params['enable_searchsuggestions']);
                    Mage::getModel('tagalys_core/config')->setTagalysConfig('search_box_selector', $params['search_box_selector']);
                    Mage::getModel('tagalys_core/config')->setTagalysConfig('suggestions_align_to_parent_selector', $params['suggestions_align_to_parent_selector']);
                    $redirect_to_tab = 'search_suggestions';
                    break;
                case 'Save Search Settings':
                    Mage::getModel('tagalys_core/config')->setTagalysConfig('module:search:enabled', $params['enable_search']);
                    if ($params['enable_search'] == '1') {
                        Mage::getModel('tagalys_core/config')->setTagalysConfig('module:search_suggestions:enabled', $params['enable_search']);
                    }
                    $redirect_to_tab = 'search';
                    break;
                case 'Save Merchandising Pages Settings':
                    Mage::getModel('tagalys_core/config')->setTagalysConfig('module:mpages:enabled', $params['enable_mpages']);
                    $redirect_to_tab = 'mpages';
                    break;
                case 'Save Similar Products Settings':
                    Mage::getModel('tagalys_core/config')->setTagalysConfig('module:similar_products:enabled', $params['enable_similarproducts']);
                    $redirect_to_tab = 'similar_products';
                    break;
                case 'Trigger full products resync now':
                    Mage::getSingleton('tagalys_core/client')->log('warn', 'Triggering full products resync');
                    foreach (Mage::helper('tagalys_core')->getStoresForTagalys() as $store_id) {
                        Mage::helper("tagalys_core/SyncFile")->triggerFeedForStore($store_id);
                    }
                    $redirect_to_tab = 'support';
                    break;
                case 'Trigger configuration resync now':
                    Mage::getSingleton('tagalys_core/client')->log('warn', 'Triggering configuration resync');
                    Mage::getModel('tagalys_core/config')->setTagalysConfig("config_sync_required", '1');
                    $redirect_to_tab = 'support';
                    break;
                case 'Restart Tagalys Setup':
                    Mage::getSingleton('tagalys_core/client')->log('warn', 'Restarting Tagalys Setup');
                    Mage::getResourceModel('tagalys_core/queue')->truncate();
                    Mage::getResourceModel('tagalys_core/config')->truncate();
                    $redirect_to_tab = 'api_credentials';
                    break;
            }
            return $this->_redirect('*/tagalys', array('_query' => 'tab='.$redirect_to_tab));
        }
    }

    public function syncmanuallyAction() {
        Mage::helper("tagalys_core/SyncFile")->sync(25);
        $syncStatus = $this->getSyncStatus();
        $this->getResponse()->setBody(json_encode($syncStatus));
    }
    public function syncstatusAction() {
        $syncStatus = $this->getSyncStatus();
        $this->getResponse()->setBody(json_encode($syncStatus));
    }

    protected function getSyncStatus() {
        $storesSyncRequired = false;
        $waitingForTagalys = false;
        $resyncScheduled = false;
        $syncStatus = array();
        $setup_status = Mage::getModel('tagalys_core/config')->getTagalysConfig('setup_status');
        $setup_complete = ($setup_status == 'completed');
        $syncStatus['setup_complete'] = $setup_complete;
        $syncStatus['stores'] = array();
        foreach (Mage::helper('tagalys_core')->getStoresForTagalys() as $key => $store_id) {
            $this_store = array();
            
            $this_store['name'] = Mage::getModel('core/store')->load($store_id)->getName();
            
            $store_setup_complete = Mage::getModel('tagalys_core/config')->getTagalysConfig("store:$store_id:setup_complete");
            $this_store['setup_complete'] = ($store_setup_complete == '1');

            $store_feed_status = Mage::getModel('tagalys_core/config')->getTagalysConfig("store:$store_id:feed_status", true);
            if ($store_feed_status != null) {
                $status_for_client = '';
                switch($store_feed_status['status']) {
                    case 'pending':
                        $status_for_client = 'Waiting to write to file';
                        $storesSyncRequired = true;
                        break;
                    case 'processing':
                        $status_for_client = 'Writing to file';
                        $storesSyncRequired = true;
                        break;
                    case 'generated_file':
                        $status_for_client = 'Generated file. Sending to Tagalys.';
                        $storesSyncRequired = true;
                        break;
                    case 'sent_to_tagalys':
                        $status_for_client = 'Waiting for Tagalys';
                        $waitingForTagalys = true;
                        break;
                    case 'finished':
                        $status_for_client = 'Finished';
                        break;
                }
                $store_resync_required = Mage::getModel('tagalys_core/config')->getTagalysConfig("store:$store_id:resync_required");
                if ($store_resync_required == '1') {
                    $resyncScheduled = true;
                    if ($status_for_client == 'Finished') {
                        $status_for_client = 'Scheduled at 1 AM';
                    }
                }
                if ($status_for_client == 'Writing to file' || $status_for_client == 'Waiting to write to file') {
                    $completed_percentage = round(((int)$store_feed_status['completed_count'] / (int)$store_feed_status['products_count']) * 100, 2);
                    $status_for_client = $status_for_client . ' (completed '.$completed_percentage.'%)';
                }
                $this_store['feed_status'] = $status_for_client;
            } else {
                $storesSyncRequired = true;
            }

            $store_updates_status = Mage::getModel('tagalys_core/config')->getTagalysConfig("store:$store_id:updates_status", true);
            $remaining_updates = Mage::getModel('tagalys_core/queue')->getCollection()->count();
            if ($this_store['setup_complete']) {
                if ($remaining_updates > 0) {
                    $storesSyncRequired = true;
                    $this_store['updates_status'] = $remaining_updates . ' remaining';
                } else {
                    if ($store_updates_status == null) {
                        $this_store['updates_status'] = 'Nothing to update';
                    } else {
                        switch($store_updates_status['status']) {
                            case 'generated_file':
                                $this_store['updates_status'] = 'Generated file. Sending to Tagalys.';
                                $storesSyncRequired = true;
                                break;
                            case 'sent_to_tagalys':
                                $this_store['updates_status'] = 'Waiting for Tagalys';
                                $waitingForTagalys = true;
                                break;
                            case 'finished':
                                $this_store['updates_status'] = 'Finished';
                                break;
                        }
                    }
                }
            } else {
                if ($remaining_updates > 0) {
                    $this_store['updates_status'] = 'Waiting for feed sync';
                } else {
                    $this_store['updates_status'] = 'Nothing to update';
                }
            }

            $syncStatus['stores'][$store_id] = $this_store;
        }
        $syncStatus['client_side_work_completed'] = false;
        $config_sync_required = Mage::getModel('tagalys_core/config')->getTagalysConfig('config_sync_required');
        if ($storesSyncRequired == true || $config_sync_required == '1') {
            if ($storesSyncRequired == true) {
                $syncStatus['status'] = 'Stores Sync Pending';
            } else {
                if ($config_sync_required == '1') {
                    $syncStatus['status'] = 'Configuration Sync Pending';
                } else {
                    // should never come here
                    $syncStatus['status'] = 'Pending';
                }
            }
        } else {
            $syncStatus['client_side_work_completed'] = true;
            if ($waitingForTagalys) {
                $syncStatus['waiting_for_tagalys'] = true;
                $syncStatus['status'] = 'Waiting for Tagalys';
            } else {
                $syncStatus['status'] = 'Fully synced';
            }
        }

        if ($resyncScheduled) {
            $syncStatus['status'] = $syncStatus['status'] . '. Resync scheduled at 1 AM. You can resync manually by using the <strong>Trigger full products resync now</strong> option in the <strong>Support & Troubleshooting</strong> tab and then clicking on the <strong>Sync Manually</strong> button that will show below.';
        }

        return $syncStatus;
    }

    protected function _saveApiCredentials($params) {
        $tagalys_api_client = Mage::getSingleton("tagalys_core/client");
        $result = $tagalys_api_client->identificationCheck(json_decode($params['api_credentials'], true));
        if ($result['result'] != 1) {
            Mage::getSingleton('core/session')->addError("Invalid API Credentials. Please try again. If you continue having issues, please email us <a href=\"mailto:cs@tagalys.com\">email us</a>.");
            return false;
        }
        // save credentials
        Mage::getModel('tagalys_core/config')->setTagalysConfig('api_credentials', $params['api_credentials']);
        Mage::getSingleton('tagalys_core/client')->cacheApiCredentials();
        return true;
    }

}