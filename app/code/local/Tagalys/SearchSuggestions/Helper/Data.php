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
    foreach($rates[$baseCurrencyCode] as $key=>$value  ) {
      if($key == $current_currency) {
       $label = Mage::app()->getLocale()->currency( $key )->getSymbol();
       if (empty($label)) {
         if($baseCurrencyCode == "INR") {
           $label = "₹";
         }
       }
      $currency_rate[] = array("id" => $key, "label" => $label, "fractional_digits" => 2 , "rounding_mode" => "round", "exchange_rate" => (float)$value, "default" => $default); //getFinalPrice
    }

  }

  return $currency_rate;
}
}
