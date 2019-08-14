<?php

class Tagalys_MerchandisingPage_Model_Product_Collection extends Mage_Core_Model_Abstract {

	protected function _construct() {
		return parent::_construct();
	}

	 public function getProductCollection()
    {
      // die('test');
    	try {
    		$service = Mage::getModel("Tagalys_MerchandisingPage_Model_Client");
        // die(var_dump($service));
    		$query = Mage::app()->getRequest()->getParam('q');
    		$q = Mage::app()->getRequest();
    		$request =  array();
    		$request = $q->getParams();
    		$request['filters'] = true;
    		$request['q'] = $request['product'];
          // $request['aadi'] = "aaaaa";
    		$payload = $request; 

    		if (!isset($request['product'])){
    			$this->_forward('noRoute');
    			return;
    		}

    		if(isset($request['order'])) {
    			$payload['sort'] = $request['order'];
    			$payload['order'] = $request['dir'];
    		} else {
          $payload['sort'] = null; //Mage::getSingleton('catalog/session')->getSortOrder();
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
        	$customer = Mage::getSingleton('customer/session')->getCustomer(); 
        	$user_id = $customer->getID();
        } 
        
        $user_id = "";
        $payload["seed"] = "";

        $device_cookie = Mage::getModel('core/cookie')->get('__ta_device');

        if (isset($payload)) {
        	$payload['visitor'] = json_encode(array("ip" => Mage::helper('core/http')->getRemoteAddr(), "seed" => $payload["seed"] , "user_id" => $user_id , "device_cookie" => $device_cookie));
        }
        

        	unset($payload['isAjax']);
        	$payload['per_page'] = $payload['limit'];
        	$payload['page'] = $payload['p'];
        	unset($payload['limit']);
        	unset($payload['product']);
        	unset($payload['p']);

        	$tagalysSearchResults = $service->merchandisingPage($payload);
      		// $tagalysSearchResults = Mage::helper('merchandisingpage')->getTagalysSearchData();
      		
      		if($tagalysSearchResults == false) {
      			return false; //parent::getProductCollection();
      		} else {
      			if(empty($tagalysSearchResults)) {
      				return false; //parent::getProductCollection();
      			}

      			$collection = $this->_productCollection = Mage::getModel('catalog/product')
      			->getCollection()
      			->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
      			->setStore(Mage::app()->getStore())
      			->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
      			->addAttributeToFilter( 'entity_id', array( 'in' => $tagalysSearchResults['results'] ) );

      			$orderString = array('CASE e.entity_id');

      			foreach($tagalysSearchResults['results'] as $i => $productId) {
      				$orderString[] = 'WHEN '.$productId.' THEN '.$i;
      			}
      			$orderString[] = 'END';
      			$orderString = implode(' ', $orderString);

      			$collection->getSelect()->order(new Zend_Db_Expr($orderString));
      			return $this->_productCollection;

      		}
      	} catch(Exception $e) {
      		// if(Mage::getStoreConfig('tagalys_merchandisingpage/default/log_status')) {
      		// 	Mage::log('Tagalys_merchandisingpage_Model_Catalogsearch_Layer::Tagalys Exception: '.json_encode($e), null, 'tagalys_exception.log');  
      		// }
      	}


      		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
      		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

      		return $collection;
    }
}
 