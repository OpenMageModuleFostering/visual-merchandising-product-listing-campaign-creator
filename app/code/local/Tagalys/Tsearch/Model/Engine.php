<?php
class Tagalys_Tsearch_Model_Engine extends Mage_Core_Model_Abstract  {

	private function _makeTagalysRequest() {
		try {
			$current_list_mode = Mage::app()->getLayout()->createBlock('catalog/product_list_toolbar')->getCurrentMode();
			
			if( $current_list_mode == "grid" || $current_list_mode == "grid-list") {
				$defaultLimit = Mage::getStoreConfig('catalog/frontend/grid_per_page');
				
			} else if($current_list_mode == "list" || $current_list_mode == "list-grid") {
				$defaultLimit = Mage::getStoreConfig('catalog/frontend/list_per_page');
			}

			$service = Mage::getSingleton("tsearch/client_connector");
			$query = Mage::app()->getRequest()->getParam('q');

			$request =  array();	
			$payload = array();
			$filters = array();
			$request = Mage::app()->getRequest()->getParams();
			$entity = 'catalog_product';

			foreach ($request as $key => $value) {
				$code = $key;
				$attr = Mage::getResourceModel('catalog/eav_attribute')
				->loadByCode($entity,$code);

				if ($attr->getId()) {
					$filters[$key] = array($request[$key]);
				}
				
			}
			if(isset($request["cat"])) {
				$filters["__categories"] = array($request["cat"]);
				// $payload["f"] = $category;
			}
			if(isset($request["qf"])) {
				foreach(explode("~",$request["qf"]) as $qf ) {
					list($k, $v) = explode('-', $qf);
					if($k  == "cat") {
					$result[ "__categories" ] = array($v);
					} else {
					$result[ $k ] = array($v);
					}
				}
				$payload["qf"] = $result;
			}
			if(isset($request["min"]) && isset($request["max"])) {
				$filters["price"] = array("min" => $request["min"], "max" => $request["max"] );
			}
			if(!empty($filters)) {
				$payload["f"] = ($filters);
			}

			//$payload['filters'] = true;
			$payload['request'] = array("results","total","filters","sort_options");
			$payload['q'] = $query;
			$session_limit = $request["limit"]; //Mage::getSingleton('catalog/session')->getLimitPage();

			
			$payload['page'] = (!empty($request['p'])) ? $request['p'] : 1;
			 if($payload['page'] == 1) {
			 	$payload['per_page'] = (!empty($session_limit) ? $session_limit : $defaultLimit) * 2;
			 } else {
		 	$payload['per_page'] = (!empty($session_limit) ? $session_limit : $defaultLimit) ;
			 }
			//$payload['per_page'] = (!empty($session_limit) ? $session_limit : $defaultLimit) ;

			//by aaditya 
			if(isset($request['order'])) {
				$payload['sort'] = $request['order']."-".$request['dir'];
				// $payload['order'] = $request['dir'];
			} else {
				$payload['sort'] = $payload['sort']; //Mage::getSingleton('catalog/session')->getSortOrder();
			}
			$user_id = "";
			$request["seed"] = "";
			if (Mage::getSingleton('customer/session')->isLoggedIn()) {
				$customer = Mage::getSingleton('customer/session')->getCustomer(); 
				$user_id = $customer->getID();
			} 

			$device_id = Mage::getModel('core/cookie')->get('__ta_device');
			$visitor_id = Mage::getModel('core/cookie')->get('__ta_visit');

			if (isset($payload)) {
				$payload['user'] = (array("ip" => Mage::helper('core/http')->getRemoteAddr(), "snapshot" => $request["snapshot"] , "visitor_id" => $visitor_id, "user_id" => $user_id , "device_id" => $device_id));
			}
		
			
			$response = $service->searchProduct($payload);
		} catch (Exception $e) {
				Mage::log('Tagalys_Tsearch_Model_Engine::Tagalys Request Error: '.$e->getMessage(), null, 'tagalys.log');
		}
	}

	public function getCatalogSearchResult() {
		$this->_makeTagalysRequest();
	}

}