<?php

class Tagalys_Core_Helper_Data extends Mage_Core_Helper_Abstract {

  public function object_to_array($obj) {
    if(is_object($obj)) $obj = (array) $obj;
    if(is_array($obj)) {
      $new = array();
      foreach($obj as $key => $val) {
        $new[$key] = $this->object_to_array($val);
      }
    }
    else $new = $obj;
    return $new;       
  }


  public function getTagalysConfig($config) {
    $configValue = Mage::getModel("tagalys_core/config")->getCollection()->addFieldToFilter('path',$config)->getFirstItem()->getData("value");
    return $configValue;
  }

  public function setTagalysConfig($config, $value) {
    $data = array('path' => $config,'value' => $value);

    $collection = Mage::getModel('tagalys_core/config')->getCollection()->addFieldToFilter('path',$config )->getFirstItem();
    if($id = $collection->getId()){
      $model = Mage::getModel('tagalys_core/config')->load($id)->addData($data);
      try {
        $model->setId($id)->save();
      } catch (Exception $e){
        Mage::log("TagalysControllerException".print_r($e->getMessage()), null, "tagalys-exception.log");
      }

    } else {
      $model = Mage::getModel("tagalys_core/config")->setData($data);
      try {
        $insertId = $model->save()->getId();
      } catch (Exception $e){
        Mage::log("TagalysControllerException".print_r($e->getMessage()), null, "tagalys-exception.log");
      }
    }
  }


  public function checkStoreInitSync($storeId) {
    $this->_helper = Mage::helper('tagalys_core');
    $status = $this->_helper->getTagalysConfig("sync-".$storeId);
    $stores = Mage::helper("sync/data")->getSelectedStore();
    $store_status = in_array($storeId, $stores);
    return $status && $store_status;
  }

  public function checkStoreIndex($storeId) {
    $this->_helper = Mage::helper('tagalys_core');
    $status = $this->_helper->getTagalysConfig("search_index_".$storeId);
    $stores = Mage::helper("sync/data")->getSelectedStore();
    $store_status = in_array($storeId, $stores);
    return $status && $store_status;
  }

  public function setupCompelete() {
    $selected_stores = Mage::helper("sync/data")->getSelectedStore();
    foreach ($selected_stores as $key => $value) {   
      $store = $this->checkStoreIndex($value);
      if (!$store) {
        return false;
      }
    }
    
    return true;
  }

  public function getTimeZoneOffset(){
    $dateTimeZoneBase = new DateTimeZone(date_default_timezone_get());
    $dateTimeZoneOff = new DateTimeZone(Mage::getStoreConfig('general/locale/timezone'));

    $dateTimeBase = new DateTime("now", $dateTimeZoneBase);
    $dateTimeOff = new DateTime("now", $dateTimeZoneOff);

    $timeOffset = $dateTimeZoneOff->getOffset($dateTimeBase);

    if($timeOffset < 0) {
      $value ="-".date('g:i',-$timeOffset);
    } else {
      $value ="+".date('g:i',$timeOffset);
    }
    return $value;
  }
}