<?php

class Tagalys_Tsearch_Model_Client_Connector extends Mage_Core_Model_Abstract {

	protected $_api_key;
	protected $_api_server;
	protected $_create_or_update_ep;
	protected $_search_ep;
	protected $_delete_ep;
	protected $_search_suggestions_ep;
	protected $_merchandising_ep;
	protected $_similar_products_ep;

	protected $_search = array();

	protected $_error = false;

	protected $timeout = 5;

	protected $visitor;


	public function getSearchResult() {
		if(!empty($this->_search)) {
			return $this->_search;
		}
		return $this->_search;
	}

	protected function _construct() {
		$this->_config = Mage::helper('tagalys_core');
		$this->_api_server = $this->_config->getTagalysConfig("api_server");
		$this->_api_key = $this->_config->getTagalysConfig("private_api_key");
		$this->_client_code =  $this->_config->getTagalysConfig("client_code");
		$this->_create_or_update_ep = Mage::getStoreConfig('tsearch/endpoint/create_or_update');
		$this->_search_ep = Mage::getStoreConfig('tsearch/endpoint/search');
		$this->_delete_ep = Mage::getStoreConfig('tsearch/endpoint/delete');
		$this->_search_suggestions_ep = Mage::getStoreConfig('tsearch/endpoint/search_suggestions');
		$this->_merchandising_ep = Mage::getStoreConfig('tsearch/endpoint/merchandising');
		$this->_similar_products_ep = Mage::getStoreConfig('tsearch/endpoint/similar_products');
	}

	protected function getUrl($e_type) {
		switch ($e_type) {
			case 'update':
			case 'create':
			$url = $this->_api_server.$this->_create_or_update_ep;
			return $url;
			break;
			case 'delete':
			$url =$this->_api_server.$this->_delete_ep;
			return $url;
			break;
			case 'search':
			$url =$this->_api_server.$this->_search_ep;
			return $url;
			break;
			case 'suggestions':
			$url = $this->_api_server.$this->_search_suggestions_ep;
			return $url;
			break;
			case 'merchandising':
			$url = $this->_api_server.$this->_merchandising_ep;
			return $url;
			break;
			case 'similar':
			$url = $this->_api_server.$this->_similar_products_ep;
			return $url;
			break;
			default:
			break;
		}

	}

	protected function createPayload($payload , $action) {
		if($action == 'search') {
			$request = array(
				'client_code' => $this->_client_code,
				'api_key' => $this->_api_key,
				'store_id' => Mage::app()->getStore()->getStoreId(),
				'currency' =>  Mage::getModel('core/cookie')->get('currency') ? Mage::getModel('core/cookie')->get('currency') : Mage::app()->getStore()->getBaseCurrencyCode()
				);	
		} else if( $action == 'merchandising' || $action == 'similar') {
			$request = array(
				'api_key' => $this->_api_key,
				'payload' => $payload
				);
		} else {
			$request = array(
				'api_key' => $this->_api_key,
				'perform' => $action,
				'payload' => $payload
				);
		}
		$payload["identification"] = $request;
		// $payloadData = json_encode($request);
		return json_encode($payload);
	}

	public function createProduct($payload) {
		try {
			$url = $this->getUrl('create');
			$payloadData = $this->createPayload($payload, $action = 'create_or_update');
			return $this->_payloadAgent($url, $payloadData);
		} catch(Exception $e) {
		}
	}

	public function updateProduct($payload) {
		try {

			$url = $this->getUrl('update');
			$payloadData = $this->createPayload($payload, $action = 'create_or_update');
			return $this->_payloadAgent($url, $payloadData);

		} catch(Exception $e) {

		}
	}

	public function deleteProduct($payload) {
		try {
			$url = $this->getUrl('delete');
			$payloadData = $this->createPayload($payload, $action = 'delete');
			return $this->_payloadAgent($url, $payloadData);
		} catch (Exception $e) {
		}
	}

	public function searchSuggestion($query = '') {
		try {
			$url = $this->getUrl('suggestions');
			$result = $this->_queryAgent($url, $query);
			return $result;	
		} catch (Exception $e) {
		}
	}

	public function searchProduct($payload, $filter = false) {

		try {
			$url = $this->getUrl('search');

			$payloadData = $this->createPayload($payload, $action = 'search');
			//var_dump($payloadData);
			//die();

			$this->_search = $this->_payloadAgent($url, ($payloadData));
			// $this->_search = json_decode(file_get_contents('response-format.json'),true);
			return $this->_search;
		} catch (Exception $e) {
		}
	}

	public function similarProduct($sku) {
		try {
			$rawurl = $this->getUrl('similar');
			$url  = str_replace(":sku", $sku, $rawurl);
			$request = array(
				'api_key' => $this->_api_key,
				);
			$this->_search = $this->_payloadAgent($url, $request);
		} catch (Exception $e) {
		}
	}

	public function merchandisingPage($payload) {
		try {
			$url = $this->getUrl('merchandising');
			$payload['api_key'] = $this->_api_key;
			$payload['filters'] = true;
			$url = str_replace(":name-of-merchandising-page",$payload['q'],$url);
			return $this->_search;
			// $this->_search = $this->_payloadAgent($url,json_encode($payload));
		} catch (Exception $e) {
			
		}
	}

	private function _getAgent($url) {
		$agent = curl_init($url);
		return $agent;
	}

	private function _queryAgent($url, $query) {
		$q_url = $url;
		$q_url .= '?q='.$query;
		$q_url .= '&api_key='.$this->_api_key;
		$agent = $this->_getAgent($url);
		curl_setopt($agent, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt( $agent, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($agent, CURLOPT_TIMEOUT, $this->timeout); 

		$result = curl_exec($agent);
		$info = curl_getinfo($agent);

		if(curl_errno($agent)) {
		
			if (curl_error($agent) === "name lookup timed out") {
				for($i = 0; $i <=2 ; $i++) {
					$this->_queryAgent($url, $query);
				}
			}
		} else {
			if (empty($result)) {
				$this->_error = false;
		
			}
			return $result;
		}
	//end of curl error log
		curl_close($agent);
	}

	private function _payloadAgent($url, $payload) {
		$agent = $this->_getAgent($url);
		curl_setopt( $agent, CURLOPT_POSTFIELDS, $payload );
		curl_setopt($agent, CURLOPT_POST,1);
		curl_setopt( $agent, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt( $agent, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $agent, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt($agent, CURLOPT_TIMEOUT, $this->timeout);
		$result = curl_exec($agent);
		$info = curl_getinfo($agent);

		if(curl_errno($agent)) {
			$this->_error = true;
		
		} else {
			if (empty($result)) {
				$this->_error = true;
	
			} 
		}
		curl_close($agent);
		if (!$this->_error) {
			$decoded = json_decode($result, true);
			return $decoded;
		} else {
			return null;
		}
	}

	public function isRequestSuccess() {
		if( $this->_error == true || empty($this->_search) || $this->_search["status"] != "OK" ) {
			return false; //ref addy
		}
		return true;
	}
}
