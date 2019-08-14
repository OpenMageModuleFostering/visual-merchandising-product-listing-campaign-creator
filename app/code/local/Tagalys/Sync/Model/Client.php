<?php
class Tagalys_Sync_Model_Client extends Mage_Core_Model_Abstract
{
	protected $_api_key;
	protected $_api_server;
	protected $_product_feed;
	protected $_sync = array();
	protected $_error = true;
	protected function _construct(){
		$this->_config = Mage::helper('tagalys_core');
		$this->_api_server = $this->_config->getTagalysConfig("api_server");
		$this->_private_api_key =  $this->_config->getTagalysConfig("private_api_key");
		$this->_client_code =  $this->_config->getTagalysConfig("client_code");
		$this->_product_dump_feed = Mage::getStoreConfig('tagalys_endpoint/endpoint/product_feed');
		$this->_product_updates_feed = Mage::getStoreConfig('tagalys_endpoint/endpoint/sync_updates');
		$this->_search_status = Mage::getStoreConfig('tagalys_endpoint/endpoint/sync_feed_progress');
		$this->_client_config = Mage::getStoreConfig('tagalys_endpoint/endpoint/client_config');
		$this->_api_auth = Mage::getStoreConfig('tagalys_endpoint/endpoint/api_auth');
	}
	protected function getUrl($e_type) {
		switch ($e_type) {
			case 'client_config':
			$url = $this->_api_server.$this->_client_config;
			return $url;
			break;
			case 'api_auth':
			$url = $this->_api_server.$this->_api_auth;
			return $url;
			break;
			case 'dump':
			$url = $this->_api_server.$this->_product_dump_feed;
			return $url;
			break;
			case 'updates':
			$url = $this->_api_server.$this->_product_updates_feed;
			return $url;
			break;
			case 'sync_feed_progress':
			$url = $this->_api_server.$this->_search_status;
			return $url;
			break;
			default:
			break;
		}
	}
	protected function createAuth($payload) {
		if(!empty($payload["store"])) {
			$request = array(
				'client_code' => $this->_client_code,
				'api_key' => $this->_private_api_key,
				'store_id' => $payload["store"] //$store_id
				);
		} else {
			$request = array(
				'client_code' => $this->_client_code,
				'api_key' => $this->_private_api_key
				);
		}

		$payload["identification"] = $request;
		return json_encode($payload);
	}

	protected function initAuth($payload) {
		$this->_api_server= $payload["api_server"];
		$request = array(
				'client_code' => $payload["client_code"],
				'private_api_key' => $payload["private_api_key"],
				'public_api_key' => $payload["public_api_key"],
				'api_server'=> $payload["api_server"]
				);
		//unset($payload);
			$payload["identification"] = $request;
		return json_encode($payload);
	}
	public function notify_tagalys($payload, $type, $init = false) {
		$this->_api_server= empty($this->_api_server)? $payload["api_server"] : $this->_api_server;
		unset($payload["key"]);
		unset($payload["form_key"]);
		try {
			if($type == "api_auth") {
				$payloadData = $this->initAuth($payload);
			} else {
				$payloadData = $this->createAuth($payload);
			}
			$url = $this->getUrl($type);
			 return $this->_payloadAgent($url, ($payloadData));
			
		} catch(Exception $e) {
		}
	}
	public function is_tagalys_search_ready($store_id) {
		try {
			$url = $this->getUrl("sync_feed_progress");
			$payload["store"] = $store_id;
			$payloadData = $this->createAuth($payload);
			return $this->_payloadAgent($url,($payloadData));
			
		} catch(Exception $e) {
		}
	}
	private function _getAgent($url) {
		$agent = curl_init($url);
		return $agent;
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
			$this->_error = false;
			if (empty($result)) {
				$this->_error = true;
			} 
		}
		curl_close($agent);

		if (!$this->_error) {
			$decoded = json_decode($result, true);
			if($decoded["status"] == "OK") {
			return $decoded;
			} else {
			return array();
			}
		} else {
			return array();
			//return json_decode($result, true);
		}
	}
}
