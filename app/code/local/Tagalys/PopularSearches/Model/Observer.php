<?php
class Tagalys_PopularSearches_Model_Observer  extends Varien_Object
{
 private $default_location; //defualt location of tagalys media directory
 private $file_path;

 protected function _construct(){ 
  $this->_config = Mage::helper('tagalys_core');
  $this->_api_server = $this->_config->getTagalysConfig("api_server");
  $this->_client_code =  $this->_config->getTagalysConfig("client_code");
  $this->_private_api_key =  $this->_config->getTagalysConfig("private_api_key");
  $this->_popular_search = Mage::getStoreConfig('tagalys_popular_searches/endpoint/popular_searches');
  $this->_url = $this->_api_server.$this->_popular_search;
  $this->default_location = Mage::getBaseDir('media'). DS .'tagalys';
  $this->file_path = $this->default_location . DS;
}
public function getPopularSearches() {
  if(!Mage::helper('tagalys_core')->getTagalysConfig("is_tsearchsuggestion_active")) {
      return false;
    }
  if(!Mage::helper('tagalys_core')->getTagalysConfig('search_complete') || !Mage::helper('tagalys_core')->getTagalysConfig('setup_complete'))
    return false;
  try {
    $request = array(
                     'client_code' => $this->_client_code,
                     'api_key' => $this->_private_api_key,
                     'store' => Mage::app()->getStore()->getStoreId()
                     );
    $payload["identification"] = $request;
    $payloadData = (json_encode($payload));
    $json_data = $this->_payloadAgent($this->_url,($payloadData));
    
    $fp = fopen( $this->file_path.'tagalys-popularsearches-'.Mage::app()->getStore()->getStoreId().'.json', 'w');
    if($json_data == null) {
     $json_data = array();
     fwrite($fp, json_encode($json_data));
     fclose($fp);
   } else {
     fwrite($fp, json_encode($json_data["popular_searches"]));
     fclose($fp);
   }

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


