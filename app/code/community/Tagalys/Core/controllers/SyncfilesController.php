<?php
class Tagalys_Core_SyncfilesController extends Mage_Core_Controller_Front_Action {

    public function _checkPrivateIdentification($identification) {
        $api_credentials = Mage::getModel('tagalys_core/config')->getTagalysConfig('api_credentials', true);
        return ($identification['client_code'] == $api_credentials['client_code'] && $identification['api_key'] == $api_credentials['private_api_key']);
    }

    public function callbackAction() {
        $params = $this->getRequest()->getParams();

        if ($this->_checkPrivateIdentification($params['identification'])) {
            $split = explode('media/tagalys/', $params['completed']);
            $filename = $split[1];
            if (is_null($filename)) {
                $split = explode('media\/tagalys\/', $params['completed']);
                $filename = $split[1];
            }
            if (is_null($filename)) {
                Mage::getSingleton('tagalys_core/client')->log('error', 'Error in callbackAction. Unable to read filename', array('params' => $params));
                return false;
            }
            Mage::helper('tagalys_core/SyncFile')->tagalysCallback($params['identification']['store_id'], $filename);
            return true;
        } else {
            Mage::getSingleton('tagalys_core/client')->log('warn', 'Invalid identification in callbackAction', array('params' => $params));
            return false;
        }
    }

}