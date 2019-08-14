<?php
class Tagalys_Sync_FeedController extends Mage_Core_Controller_Front_Action
{

  public function deleteAction() {
    try {
      $output = $this->getRequest()->getParams();
      $temp = explode("media\/tagalys\/", $output["completed"]);
      $feed = $temp[1];
      $this->_feed = Mage::helper("sync/tagalysFeedFactory");
      $this->_feed->deleteProductFeed($output["feed"]);
      return true;
    } catch (Exception $e) {
      return false;
    }
  }

  public function indexStatusAction() {

    try {
      $output = $this->getRequest()->getParams();

      if($output["identification"]["client_code"] == Mage::helper('tagalys_core')->getTagalysConfig('client_code') 
         && $output["identification"]["api_key"] == Mage::helper('tagalys_core')->getTagalysConfig('private_api_key')) {
        Mage::helper('tagalys_core')->setTagalysConfig('search_index_'.$output["identification"]["store_id"], 1);

      $temp = explode("media/tagalys/", $output["completed"]);
      $feed = $temp[1];
      if (is_null($feed)){
        $temp = explode("media\/tagalys\/", $output["completed"]);
        $feed = $temp[1];
      }
      Mage::helper("sync/tagalysFeedFactory")->deleteProductFeed($feed);


      $type_file = explode("-", $feed);
      if( $type_file[1] == "dump") {
        Mage::helper('tagalys_core')->setTagalysConfig('product_sync_required_'.$output["identification"]["store_id"], 0);
      }

      unlink(Mage::getBaseDir("media"). DS ."tagalys" . DS."tagalys-sync-progress-".$output["identification"]["store_id"].".json");
     
      $plugin_to_be_activated = Mage::getStoreConfig('tagalys_endpoint/endpoint/plugin_to_be_activated');
      foreach (explode(",", $plugin_to_be_activated) as $key => $value) {
        Mage::helper('tagalys_core')->setTagalysConfig('is_'.$value.'_active', 1);
     }
     echo json_encode(array("installed_plugin" => Mage::getStoreConfig('tagalys_endpoint/endpoint/installed_plugin')));
   } else {
    return false;
  }

} catch (Exception $e) {
  return false;
}

if(Mage::helper('tagalys_core')->setupCompelete()){
  Mage::helper('tagalys_core')->setTagalysConfig('setup_complete', 1);
  Mage::helper('tagalys_core')->setTagalysConfig('search_complete', 1);

}
}

public function progressAction() {
  try {
    $output = $this->getRequest()->getParams();
    $storeId = (int)$output["store"];
    $this->_feed = Mage::helper("sync/tagalysFeedFactory");
    $dump_feed = $this->_feed->getFeedFile($storeId, "dump");
    
    if(!is_null($dump_feed)) {
      if($this->_feed->getFeedProgress($storeId, $dump_feed) == 0){
        $message =  "Waiting for feed to be processed by cron.";
      } else {
        $message = $this->_feed->getFeedProgress($storeId, $dump_feed);
      }

    } else {
      if(Mage::helper("tagalys_core")->checkStoreInitSync($storeId)) {
        $message = "Completed" ;
      }else{
        $message = "Waiting for feed to be processed.";
      }
    }

    echo ($message);
    exit;
  } catch (Exception $e) {
    return false;
  }
}

public function searchStatusAction() {
  try {
    $output = $this->getRequest()->getParams();
    $storeId = (int)$output["store"];
    if(Mage::helper("tagalys_core")->checkStoreInitSync($storeId)) {
      $service = Mage::getSingleton("sync/client");
        $is_search_ready = ($service->is_tagalys_search_ready($storeId));//to-do
        if(isset($is_search_ready) && (int)$is_search_ready["completed"] > 0) {
          $percent = 100 - (((int)$is_search_ready["total"] - (int)$is_search_ready["completed"]) / ((int)$is_search_ready["total"])) * 100;
          $percent = number_format( (int)$percent, 0 );
        } else {
          $percent = "Waiting for response from Tagalys... ";
        }
        
      } else {
        $percent = "Waiting for feed creation to be completed.";
      }

      echo $percent;
      
    } catch (Exception $e) {
      return false;
    }
  }

  public function enableButtonAction() {
    echo (int)Mage::helper('tagalys_core')->getTagalysConfig('setup_complete') && Mage::helper('tagalys_core')->getTagalysConfig("search_complete");
    exit;
  }


}

