<?php 

class Tagalys_SearchSuggestions_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getCurrentCurrency() {
        $currency_rate = array();
        $currencyModel = Mage::getModel('directory/currency');
        $currencies = $currencyModel->getConfigAllowCurrencies(); // abaliable currency
        $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode(); //default code
        $defaultCurrencies = $currencyModel->getConfigBaseCurrencies();
        $rates=$currencyModel->getCurrencyRates($defaultCurrencies, $currencies); //rates of each currency
        $current_currency = Mage::getModel('core/cookie')->get('currency') ? Mage::getModel('core/cookie')->get('currency') : Mage::app()->getStore()->getBaseCurrencyCode();
        if (empty($rates[$baseCurrencyCode])) {
            $rates[$baseCurrencyCode] = array($baseCurrencyCode => '1.0000');
        }
        foreach($rates[$baseCurrencyCode] as $key=>$value) {
            $default = $baseCurrencyCode == $key ? true : false;
            if ($key == $current_currency) {
                $label = Mage::app()->getLocale()->currency($key)->getSymbol();
                if (empty($label)) {
                    if ($baseCurrencyCode == "INR") {
                        $label = "₹";
                    }
                }
                $currency_rate[] = array("id" => $key, "label" => $label, "fractional_digits" => 2 , "rounding_mode" => "round", "exchange_rate" => (float)$value, "default" => $default); //getFinalPrice
            }
        }
        return $currency_rate;
    }

    public function cachePopularSearches() { 
        try {
            $setup_status = Mage::getModel('tagalys_core/config')->getTagalysConfig('setup_status');
            $setup_complete = ($setup_status == 'completed');
            if ($setup_complete) {
                $stores_for_tagalys = Mage::helper('tagalys_core')->getStoresForTagalys();
                if ($stores_for_tagalys != null) {
                    foreach ($stores_for_tagalys as $store_id) {
                        $popular_searches = Mage::getSingleton('tagalys_core/client')->storeApiCall($store_id, '/v1/popular_searches');
                        if ($popular_searches != false && array_key_exists('popular_searches', $popular_searches)) {
                            Mage::getModel('tagalys_core/config')->setTagalysConfig("store:{$store_id}:popular_searches", $popular_searches['popular_searches'], true);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Mage::log("Error in cachePopularSearches: ". $e->getMessage(), null, "tagalys.log");
        }
        return $this;
    }
}
