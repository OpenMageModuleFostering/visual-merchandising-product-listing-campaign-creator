<?php
class Tagalys_Core_Model_Observer extends Varien_Object {
    public function tagalys_distpatch(Varien_Event_Observer $observer) { 
        try {
            $params = $observer->getEvent()->getControllerAction()->getRequest()->getParams();
            $tagalys_config_events = array('adminhtml_catalog_product_attribute_delete','adminhtml_catalog_product_attribute_save', 'adminhtml_system_currency_saveRates','adminhtml_system_currencysymbol_save');
            if (in_array ($observer->getEvent()->getControllerAction()->getFullActionName(), $tagalys_config_events)) {
                // sync config
                Mage::getModel('tagalys_core/config')->setTagalysConfig("config_sync_required", '1');
            }
            if (in_array ($observer->getEvent()->getControllerAction()->getFullActionName(), array("adminhtml_catalog_category_save"))) {
                $stores = Mage::helper('tagalys_core')->getStoresForTagalys();
                foreach($stores as $i => $store_id) {
                    Mage::helper("tagalys_core/SyncFile")->triggerFeedForStore($store_id);
                }
            }
            if (isset($params) && isset($params['section']) && $params["section"] == "currency") {
                // sync config
                Mage::getModel('tagalys_core/config')->setTagalysConfig("config_sync_required", 1);
            }
        } catch (Exception $e) {
            Mage::log("Error in tagalys_distpatch: ". $e->getMessage(), null, "tagalys.log");
        }
    }

    public function syncCron() {
        $utc_now = new DateTime("now", new DateTimeZone('UTC'));
        $time_now = $utc_now->format(DateTime::ATOM);
        $cron_heartbeat_sent = Mage::getModel('tagalys_core/config')->getTagalysConfig("cron_heartbeat_sent");
        if ($cron_heartbeat_sent == false) {
            Mage::getSingleton('tagalys_core/client')->log('info', 'Cron heartbeat');
            $cron_heartbeat_sent = Mage::getModel('tagalys_core/config')->setTagalysConfig("cron_heartbeat_sent", true);
        }
        Mage::getModel('tagalys_core/config')->setTagalysConfig("heartbeat:cron", $time_now);
        Mage::helper("tagalys_core/SyncFile")->sync();
    }

    public function resyncCron() {
        $utc_now = new DateTime("now", new DateTimeZone('UTC'));
        $time_now = $utc_now->format(DateTime::ATOM);
        Mage::getModel('tagalys_core/config')->setTagalysConfig("heartbeat:resyncCron", $time_now);
        $stores = Mage::getModel('tagalys_core/config')->getTagalysConfig("stores", true);
        if ($stores != NULL) {
            foreach ($stores as $i => $store_id) {
                $resync_required = Mage::getModel('tagalys_core/config')->getTagalysConfig("store:$store_id:resync_required");
                if ($resync_required == '1') {
                    $this->triggerFeedForStore($store_id);
                    Mage::getModel('tagalys_core/config')->setTagalysConfig("store:$store_id:resync_required", '0');
                }
            }
        }
    }

    // create / update / delete from admin
    public function productChanged(Varien_Event_Observer $observer) {
        try {
            $product = $observer->getEvent()->getProduct();
            $product_id = $product->getId();
            Mage::getModel('tagalys_core/queue')->blindlyAddProduct($product_id);
        } catch (Exception $e) {
            Mage::log("Exception on productChanged: " . $e->getMessage(), null, "tagalys.log");
        }
    }

    // csv import
    public function productsImported(Varien_Event_Observer $observer) {
        try {
            $adapter = $observer->getEvent()->getAdapter();
            $affectedEntityIds = $adapter->getAffectedEntityIds();
            foreach($affectedEntityIds as $product_id) {
                Mage::getModel('tagalys_core/queue')->blindlyAddProduct($product_id);
            }
        } catch (Exception $e) {
            Mage::log("Exception on productsImported: " . $e->getMessage(), null, "tagalys.log");
        }
    }
}