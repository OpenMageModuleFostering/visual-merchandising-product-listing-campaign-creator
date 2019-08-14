<?php
class Tagalys_Sync_Model_Observer extends Varien_Object
{
  protected $_queue;
  protected $_feed;
  protected $_helper;
  protected $_bulkImport;
  function __construct()
  {
    $this->_config = Mage::helper('tagalys_core');
    $this->_status = $this->_config->getTagalysConfig("is_tagalys_active");
    $this->_queue = Mage::getModel('sync/queue');
    $this->_feed = Mage::helper("sync/tagalysFeedFactory");
    $this->_helper = Mage::helper("sync/service");
    $this->_sync_helper = Mage::helper("sync/data");
    
  }
  public function updatesCron() {
    $queue_size =  Mage::getModel('sync/queue')->getCollection()->getSize();
    if($queue_size > 0) {
      foreach ($this->_sync_helper->getSelectedStore() as $key) {
        $response = $this->_feed->getUpdatesDump($key);
      }
    }
    return true; 
  }

  public function resyncCron() {
    foreach ($this->_sync_helper->getSelectedStore() as $key) {
      $resync_required =   Mage::helper('tagalys_core')->getTagalysConfig('product_sync_required_'.$key);
      if($resync_required) {
        $response = Mage::helper("sync/tagalysFeedFactory")->createProductFeed($key,true);;
      }
    }

    return true;
  }
  public function createProductFeed(Mage_Cron_Model_Schedule $schedule)
  {
    $feeds = $this->_feed->getAllProductFeed();
    if(!empty($feeds)) {
      $this->_feed->updateProductFeed();
    }
  }
  public function productDelete(Varien_Event_Observer $observer)
  {
    try {
      $product = $observer->getEvent()->getProduct();
      $product_id = $product->getId(); 
      if(!$product_id) {
        return;
      }
      //Check already record is exists in queue table
      $existingProduct = $this->_queue->load($product_id,'product_id');
      $_id = $existingProduct->getId();
      if(empty($_id)) {
        $data = array(
                      "product_id" => $product_id
                      );
        $this->_queue->setData($data);
        try {
          $queue_id = $this->_queue->save()->getId();
        } catch(Exception $e) {
          Mage::log("Sync Queue: error adding product [ ".$product." ] in queue", null, "tagalys.log");
        }
      } else {
        Mage::log("Sync: product already in queue [ ". $product_id." ]", null, "tagalys.log");
      }
    } catch (Exception $e) {
      Mage::log("Sync: product delete error", null, "tagalys.log");
    } 
  }
  public function productUpdate(Varien_Event_Observer $observer)
  {
    try {
      $product = $observer->getEvent()->getProduct();
      $product_id = $product->getId(); 
      if(!$product_id) {
        return;
      }
      //Check already record is exists in queue table
      $existingProduct = $this->_queue->load($product_id,'product_id');
      $_id = $existingProduct->getId();
      if(empty($_id)) {
        $data = array(
                      "product_id" => $product_id
                      );
        $this->_queue->setData($data);
        try {
          $queue_id = $this->_queue->save()->getId();
        } catch(Exception $e) {
          Mage::log("Sync Queue: error adding product [ ".$product_id." ] in queue", null, "tagalys.log");
        }
      } 
    } catch (Exception $e) {
      Mage::log("Sync: product update error", null, "tagalys.log");
    }
  }
  public function saleOrderComplete(Varien_Event_Observer $observer)
  {
    try {
      $payment = $observer->getEvent()->getPayment();
      $items = $payment->getOrder()->getItemsCollection();
      foreach($items as $item) {
        $product = $item->getProduct();
        $product_id = $product->getId(); 
        if(!$product_id) {
          return;
        }
        //Check already record is exists in queue table
        $existingProduct = $this->_queue->load($product_id,'product_id');
        $_id = $existingProduct->getId();
        if(empty($_id)) {
          $data = array(
                        "product_id" => $product_id
                        );
          $this->_queue->setData($data);
          try {
            $queue_id = $this->_queue->save()->getId();
          } catch(Exception $e) {
            Mage::log("Sync Queue: error adding product [ ".$product_id." ] in queue", null, "tagalys.log");
          }
        }
      }
    } catch (Exception $e) {
      Mage::log("Sync: ". $e->getMessage(), null, "tagalys.log");
    }
  }
  public function productImportByDataFlowStart(Varien_Event_Observer $observer)
  {
    Mage::log("Product Import By Data Flow Start", null, "tagalys.log");
  } 
  public function getProductDump(Varien_Event_Observer $observer) 
  {
    $this->_feed->updateProductFeed();
  }
  public function productsImported(Varien_Event_Observer $observer) {
    try {
      if($this->_bulkImport == "enable") {
        return true;
      } 
      $products = array();
      $adapter_data = $observer->getEvent()->getData('adapter');
      Mage::log($observer->getEvent(), null, 'sync.log');
      $behavior = $adapter_data->getBehavior();
      if ($behavior == "delete") {
       while ($bunch = $adapter_data->getNextBunch()) {
        foreach ($bunch as $rowNum => $rowData) {
         $products[] = 'sku-'.$rowData['sku']; 
       }
     }
   } else {
     $products = $adapter_data->getAffectedEntityIds();
   }
   if(!empty($products)) {
     foreach ($products as $key => $value) {
      $existingProduct = Mage::getModel('sync/queue')->load($value,'product_id');
      $_id = $existingProduct->getId();
      if(!isset($_id)) {
       $data = array(
                     "product_id" => $value
                     );
       $this->_queue->setData($data);
       $queue_id = $this->_queue->save()->getId();
     }
   }
 }
} catch (Exception $e) {
  Mage::log("Sync: ". $e->getMessage(), null, "tagalys.log");
}
}
}
