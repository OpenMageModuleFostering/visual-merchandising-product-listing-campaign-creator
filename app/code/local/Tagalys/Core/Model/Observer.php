<?php
class Tagalys_Core_Model_Observer  extends Varien_Object
{

  public function tagalys_distpatch(Varien_Event_Observer $observer)
  { 
    if(!Mage::helper('tagalys_core')->getTagalysConfig("is_tsearchsuggestion_active")) {
      return false;
    }
    $params = $observer->getEvent()->getControllerAction()->getRequest()->getParams();
    $tagalys_config_events = array('adminhtml_catalog_product_attribute_delete','adminhtml_catalog_product_attribute_save', 'adminhtml_system_currency_saveRates','adminhtml_system_currencysymbol_save');
    if(in_array ($observer->getEvent()->getControllerAction()->getFullActionName(), $tagalys_config_events))
    {
      Mage::dispatchEvent("tagalys_custom_config_event", array('request' => $observer->getControllerAction()->getRequest()));
    }
    if(in_array ($observer->getEvent()->getControllerAction()->getFullActionName(), array("adminhtml_catalog_category_save")))
    {

      $catid = $params["id"];
      $this->updateCategory($catid);
    }
    if($params["section"] == "currency") {
      Mage::dispatchEvent("tagalys_custom_config_event", array('request' => $observer->getControllerAction()->getRequest()));
    }
  }

  public function updateCategory($catid) {
    $this->_queue = Mage::getModel('sync/queue');
    try {
      $category = Mage::getModel('catalog/category')->setId($catid);
      $products = Mage::getResourceModel('catalog/product_collection')
      ->addCategoryFilter($category)
      ->getAllIds();
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

   return true;

 } catch (Exception $e) {
  Mage::log("Sync: ". $e->getMessage(), null, "tagalys.log");
}

}

public function ValidateClientConfig() {
		// $config_data = $observer->getObject();
 
  $tagalys_response = $this->getTagalysConfig();
  if($tagalys_response["result"] != true ) { 
    Mage::helper('tagalys_core')->setTagalysConfig('stores_setup', 0);
    $tagalys_response["message"] = empty($tagalys_response["message"]) ? "Something went wrong. Please write to us at cs@tagalys.com" : $tagalys_response["message"];
    Mage::getSingleton('core/session')->addError("NOTICE:". $tagalys_response["message"]);
    Mage::app()->getResponse()->setRedirect(Mage::helper("adminhtml")->getUrl("adminhtml/tagalys/index"));
    Mage::app()->getResponse()->sendResponse();
    exit;
  } else {
    $this->startInitialSync();
    return true;
  }
   // Mage::dispatchEvent('start_init_sync', array('object'=> []));

}

public function getTagalysConfig() {

  $service = Mage::getSingleton("sync/client");
  $this->_helper = Mage::helper("sync/service");
  $stores = Mage::helper("sync/data")->getSelectedStore();
  if (empty($stores)) {
    return false;
  }
  foreach ($stores as $key => $value) {
    $locale = Mage::getStoreConfig('general/locale/code', $value);
    $language= substr(Mage::getStoreConfig('general/locale/code', $value),0,2);
    $temp_currency_data =  $this->_helper->getClientSetData();
    $client_config[] = array(
                             "id" => $value, 
                             "label" => Mage::getModel('core/store')->load($value)->getName(), 
                             "locale" => $locale, 
                             "multi_currency_mode"=>"exchange_rate", 
                             "currencies" => $this->_helper->getClientCurrencyData(),
                             "fields" => $temp_currency_data["fields"],
                             "tag_sets" => $temp_currency_data["tag_set"],
                             "sort_options" =>  $this->_helper->getClientSortOptions(),
                             "products_count" => Mage::helper("sync/tagalysFeedFactory")->getTotal($value, true));
  }


     $tagalys_response = $service->notify_tagalys(array("stores" => $client_config), "client_config", true);//to-do 

     if($tagalys_response["result"] == true) {
      if(!empty($tagalys_response["product_sync_required"])) {
        foreach ($tagalys_response["product_sync_required"] as $key => $value) {
          # code...
          Mage::helper('tagalys_core')->setTagalysConfig('product_sync_required_'.$key, (int)$value);
        }

      }
    }
    return $tagalys_response;
  }

  public function authClient(Varien_Event_Observer $observer) {
   $auth_data = $observer->getObject();
   $service = Mage::getSingleton("sync/client");
  	$tagalys_response = $service->notify_tagalys($auth_data, "api_auth", true);//to-do 
  	if($tagalys_response["result"] != true ){ //to-do
     Mage::helper('tagalys_core')->setTagalysConfig('is_tagalys_active', 0);
     $tagalys_response["message"] = empty($tagalys_response["message"]) ? "Invalid Credentials. For any help, please write to us at cs@tagalys.com" : $tagalys_response["message"];
     Mage::getSingleton('core/session')->addError("NOTICE:".$tagalys_response["message"]);
     Mage::app()->getResponse()->setRedirect(Mage::helper("adminhtml")->getUrl("adminhtml/tagalys/index"));
     Mage::app()->getResponse()->sendResponse();
     exit;
   } else {
     Mage::helper('tagalys_core')->setTagalysConfig('is_tagalys_active', 1);
     Mage::getSingleton('core/session')->addSuccess("NOTICE: Tagalys Authentication Success");

   }
   return true;
 }



 public function startInitialSync(){
  $this->_sync_helper = Mage::helper("sync/data");
  foreach ($this->_sync_helper->getSelectedStore() as $key) {
    $response = Mage::helper("sync/tagalysFeedFactory")->getProductDump($key);
  }
}

}

?>