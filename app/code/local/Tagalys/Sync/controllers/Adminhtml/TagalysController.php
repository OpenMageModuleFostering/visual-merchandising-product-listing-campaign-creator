<?php

class Tagalys_Sync_Adminhtml_TagalysController extends Mage_Adminhtml_Controller_Action
{
  const CRON_STRING_PATH = 'crontab/jobs/tagalys_updates_cron/schedule/cron_expr';
  const CRON_FEED_STRING_PATH = 'crontab/jobs/tagalys_resync_cron/schedule/cron_expr';

  public function indexAction() {

    $this->_registryObject();
    $this->_title('Tagalys Configuration');
    $this->loadLayout();
    $this->_setActiveMenu('Tagalys/core');
    $this->renderLayout();
  }

  public function saveAction() {


    $output = $this->getRequest()->getParams();
    
    $this->_helper = Mage::helper('tagalys_core');
    $is_tagalys_active = $this->_helper->getTagalysConfig("is_tagalys_active");
  
    if(!empty($output["submit_auth"])){
      Mage::dispatchEvent('tagalys_auth_event', array('object'=>$output));
    }
    

    unset($output["key"]);
    unset($output["form_key"]);

    if(!empty($output["submit_signup_next"])) {
      Mage::helper('tagalys_core')->setTagalysConfig("is_signup", 1);
    }
    if(empty($output["tagalys_updates_cron_time"])) {
      $output["tagalys_updates_cron_time"] = "*/1 * * * *";
    }
    if(!empty($output['feed_cron_time'])) {
      $output['feed_cron_time']  = $output['feed_cron_time'][1].' '.$output['feed_cron_time'][0].' * * *';
    } else {
      $output['feed_cron_time'] = "00 01 * * *";
    }
    if(empty($output["search_box"])) {
      $output["search_box"] = "#search";
    }

    if(!empty($output["tagalys_updates_cron_time"])) {
      try {
       Mage::getModel('core/config_data')
       ->load(self::CRON_STRING_PATH, 'path')
       ->setValue($output["tagalys_updates_cron_time"])
       ->setPath(self::CRON_STRING_PATH)
       ->save();

         Mage::getModel('core/config_data')
       ->load(self::CRON_FEED_STRING_PATH, 'path')
       ->setValue($output["tagalys_updates_cron_time"])
       ->setPath(self::CRON_FEED_STRING_PATH)
       ->save();
     }catch (Exception $e){
      Mage::log("TagalysControllerException".print_r($e->getMessage()), null, "tagalys-exception.log");
    }
  }
  if(!empty($output["stores_setup"])) {
    $output["stores_setup"] = implode(",", $output["stores_setup"]);
  }

  foreach ($output as $key => $value) {
    if(!preg_match("/^submit_*/i", $key)){
      Mage::helper('tagalys_core')->setTagalysConfig($key, $value);
    }
  }
  if(!empty($output["submit_config"]) && $output["submit_config"] != "Save settings") {
    Mage::dispatchEvent('tagalys_client_config', array('object'=>$output));
  }
  if(!empty($output["submit_resync"])) {
    $this->_sync_helper = Mage::helper("sync/data");
    foreach ($this->_sync_helper->getSelectedStore() as $key) {
      $response = Mage::helper("sync/tagalysFeedFactory")->createProductFeed($key,true);;
    }
  }
  if(!empty($output["submit_reconfig"])) {
    Mage::dispatchEvent('tagalys_client_config', array('object'=>$output));
  }
  if(empty($output["submit_resync"]) && empty($output["submit_reconfig"])) {
  if(Mage::helper('tagalys_core')->getTagalysConfig("is_tsearchsuggestions_active") || Mage::helper('tagalys_core')->getTagalysConfig("is_tsearch_active"))
  Mage::getSingleton('core/session')->addSuccess("Your preference has been saved.");
  } else {
    Mage::getSingleton('core/session')->addSuccess($output["submit_resync"] ? $output["submit_resync"]." Success." : $output["submit_reconfig"]. " Success.");
  }
  return $this->_redirect('*/tagalys');
}

    /**
   * registry form object
   */
    protected function _registryObject() {
    // Mage::register('sync', Mage::getModel('sync/form'));
    }

    public function initialSyncAction() {
      try {
        Mage::getModel('sync/observer')->startInitialSync();
      } catch (Exception $e){
        Mage::log("TagalysControllerException".print_r($e->getMessage()), null, "tagalys-exception.log");
      }
      return $this->_redirectReferer();
    }
  }