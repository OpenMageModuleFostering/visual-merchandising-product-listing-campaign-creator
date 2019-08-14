<?php
class Tagalys_Core_Model_Config extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init("tagalys_core/config");
    }

    public function checkStatusCompleted() {
        $setup_status = $this->getTagalysConfig('setup_status');
        if ($setup_status == 'sync') {
            $store_ids = Mage::helper("tagalys_core")->getStoresForTagalys();
            $all_stores_completed = true;
            foreach($store_ids as $store_id) {
                $store_setup_status = $this->getTagalysConfig("store:$store_id:setup_complete");
                if ($store_setup_status != '1') {
                    $all_stores_completed = false;
                    break;
                }
            }
            if ($all_stores_completed) {
                $this->setTagalysConfig("setup_status", 'completed');
                $package_name = Mage::getStoreConfig('tagalys/package/name');
                $modules_to_activate = array();
                switch($package_name) {
                    case 'Search Suggestions':
                        $modules_to_activate = array('search_suggestions');
                        break;
                    case 'Search':
                        $modules_to_activate = array('search_suggestions', 'search');
                        break;
                    case 'Mpages':
                        $modules_to_activate = array('mpages');
                        break;
                }
                Mage::getSingleton('tagalys_core/client')->log('info', 'All stores synced. Enabling Tagalys features.', array('modules_to_activate' => $modules_to_activate));
                foreach($modules_to_activate as $module_to_activate) {
                    $this->setTagalysConfig("module:$module_to_activate:enabled", '1');
                }
            }
        }
    }

    public function getTagalysConfig($config, $json_decode = false) {
        $configValue = $this->getCollection()->addFieldToFilter('path',$config)->getFirstItem()->getData("value");
        if ($configValue === NULL) {
            $defaultConfigValues = array(
                'setup_status' => 'api_credentials',
                'search_box_selector' => '#search'
            );
            if (array_key_exists($config, $defaultConfigValues)) {
                $configValue = $defaultConfigValues[$config];
            } else {
                $configValue = NULL;
            }
        }
        if ($configValue !== NULL && $json_decode) {
            return json_decode($configValue, true);
        }
        return $configValue;
    }

    public function setTagalysConfig($config, $value, $json_encode = false) {
        if ($json_encode) {
            $value = json_encode($value);
        }
        $data = array('path' => $config, 'value' => $value);

        $collection = $this->getCollection()->addFieldToFilter('path',$config)->getFirstItem();

        try {
            if ($id = $collection->getId()){
                $model = $this->load($id)->addData($data);
                $model->setId($id)->save();
            } else {
                $model = Mage::getModel("tagalys_core/config")->setData($data);
                $insertId = $model->save()->getId();
            }
        } catch (Exception $e){
            Mage::getSingleton('tagalys_core/client')->log('error', 'Exception in setTagalysConfig', array('exception_message' => $e->getMessage()));
        }
    }
}