<?php
class Tagalys_Core_Model_Client extends Mage_Core_Model_Abstract {
    protected function _construct(){
        $this->_api_timeout = 60;
        $this->cacheApiCredentials();
    }
    public function cacheApiCredentials() {
        try {
            $api_credentials = json_decode(Mage::getModel('tagalys_core/config')->getTagalysConfig("api_credentials"), true);
            $this->_api_server = $api_credentials['api_server'];
            $this->_client_code = $api_credentials['client_code'];
            $this->_private_api_key = $api_credentials['private_api_key'];
            $this->_public_api_key = $api_credentials['public_api_key'];
        } catch (Exception $e) {
            $this->_api_server = false;
        }
    }
    public function identificationCheck($api_credentials) {
        try {
            $this->_api_server = $api_credentials['api_server'];
            $response = $this->_apiCall('/v1/identification/check', array(
                'identification' => array(
                    'client_code' => $api_credentials['client_code'],
                    'public_api_key' => $api_credentials['public_api_key'],
                    'private_api_key' => $api_credentials['private_api_key']
                )
            ));
            return $response;
        } catch (Exception $e) {
         Mage::log("Exception in Client.php identificationCheck: {$e->getMessage()}; api_credentials: " . json_encode($api_credentials), null, 'tagalys.log');
         return false;
        }
    }
    public function log($level, $message, $data = null) {
        Mage::log(json_encode(compact('level', 'message', 'data')), null, 'tagalys.log');
        if ($this->_api_server != false && $level != 'local') {
            $log_params = array('log_level' => $level, 'log_message' => $message);
            if ($data != null) {
                if (array_key_exists('store_id', $data)) {
                    $log_params['log_store_id'] = $data['store_id'];
                    unset($data['store_id']);
                }
                $log_params['log_data'] = $data;
            }
            $this->clientApiCall('/v1/clients/log', $log_params);
        }
    }
    public function clientApiCall($path, $params) {
        $params['identification'] = array(
            'client_code' => $this->_client_code,
            'api_key' => $this->_private_api_key
        );
        return $this->_apiCall($path, $params);
    }
    public function storeApiCall($store_id, $path, $params) {
        $params['identification'] = array(
            'client_code' => $this->_client_code,
            'api_key' => $this->_private_api_key,
            'store_id' => $store_id
        );
        return $this->_apiCall($path, $params);
    }
    private function _apiCall($path, $params) {
        try {
            if ($this->_api_server === false) {
                Mage::log("Error in Client.php _apiCall: this->_api_server is false; path: $path; params: " . json_encode($params), null, 'tagalys.log');
                return false;
            }
            if (array_key_exists('identification', $params)) {
                $platform_version_info = Mage::getVersionInfo();
                $params['identification']['api_client'] = array('platform' => 'Magento-' . Mage::getEdition() . '-' . $platform_version_info['major'], 'platform_version' => Mage::getVersion(), 'plugin' => Mage::getStoreConfig('tagalys/package/name'), 'plugin_version' => Mage::getStoreConfig('tagalys/package/version'));
            }
            $url = $this->_api_server . $path;
            $curl_handle = curl_init($url);
            $port = parse_url($url, PHP_URL_PORT);
            if ($port != NULL) {
                curl_setopt($curl_handle, CURLOPT_PORT, $port);
            }
            curl_setopt($curl_handle, CURLOPT_POST, 1);
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl_handle, CURLOPT_TIMEOUT, $this->_api_timeout);
            $response = curl_exec($curl_handle);
            if (curl_errno($curl_handle)) {
                $http_code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
                Mage::log("Error in Client.php _apiCall: curl error ($http_code); api_server: $this->_api_server; path: $path; params: " . json_encode($params), null, 'tagalys.log');
                return false;
            }
            if (empty($response)) {
                Mage::log("Error in Client.php _apiCall: response is empty; api_server: $this->_api_server; path: $path; params: " . json_encode($params), null, 'tagalys.log');
                return false;
            }
            curl_close($curl_handle);
            $decoded = json_decode($response, true);
            if ($decoded === NULL) {
                Mage::log("Error in Client.php _apiCall: decoded is NULL; api_server: $this->_api_server; path: $path; params: " . json_encode($params), null, 'tagalys.log');
                return false;
            }
            if ($decoded["status"] == "OK") {
                return $decoded;
            } else {
                return false;
            }
        } catch (Exception $e) {
            Mage::log("Exception in Client.php _apiCall: {$e->getMessage()}; api_server: $this->_api_server; path: $path; params: " . json_encode($params), null, 'tagalys.log');
            return false;
        }
    }
}
