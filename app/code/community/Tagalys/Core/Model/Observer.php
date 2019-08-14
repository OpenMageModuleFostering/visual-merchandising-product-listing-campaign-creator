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
        Mage::helper("tagalys_core/SyncFile")->cron();
    }

    public function resyncCron() {
        Mage::helper("tagalys_core/SyncFile")->resyncCron();
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