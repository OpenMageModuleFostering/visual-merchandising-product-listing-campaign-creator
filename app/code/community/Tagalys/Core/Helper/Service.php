<?php
class Tagalys_Core_Helper_Service extends Mage_Core_Helper_Abstract {
    protected $_storeId;

    public function getProductPayload($product_id, $store_id, $admin = false) {
        $core_helper = Mage::helper('tagalys_core');
        $details_model = Mage::getModel("tagalys_core/productDetails");

        $product_data = new stdClass();
        $utc_now = new DateTime("now", new DateTimeZone('UTC'));
        $time_now =  $utc_now->format(DateTime::ATOM);
        $attr_data = array();
        $attributes = $details_model->getProductAttributes($product_id, $store_id, array_keys((array) $product_data));
        $product_data = $details_model->getProductFields($product_id, $store_id);
        $product_data->synced_at = $time_now;
        $product_data->__tags = $attributes;

        return $product_data;
    }

    public function syncClientConfiguration($stores = false) {
        $api_client = Mage::getSingleton('tagalys_core/client');
        $client_config = $this->getClientConfiguration($stores);

        $tagalys_response = $api_client->clientApiCall('/v1/configuration', $client_config);

        if ($tagalys_response === false) {
            return false;
        }

        if ($tagalys_response['result'] == true) {
            if (!empty($tagalys_response['product_sync_required'])) {
                foreach ($tagalys_response['product_sync_required'] as $store_id => $required) {
                    Mage::getModel('tagalys_core/config')->setTagalysConfig("store:{$store_id}:resync_required", (int)$required);
                }
            }
        }
        
        return $tagalys_response;
    }

    public function getClientConfiguration($stores = false) {
        if ($stores == false) {
            $stores = Mage::helper("tagalys_core")->getStoresForTagalys();
        }
        if (empty($stores)) {
            return false;
        }
        $client_configuration = array('stores' => array());
        foreach ($stores as $index => $store_id) {
            $locale = Mage::getStoreConfig('general/locale/code', $store_id);
            $tag_sets_and_custom_fields = $this->getTagSetsAndCustomFields($store_id);
            $client_configuration['stores'][] = array(
                'id' => $store_id, 
                'label' => Mage::getModel('core/store')->load($store_id)->getName(),
                'locale' => $locale, 
                'multi_currency_mode' => 'exchange_rate',
                'currencies' => $this->getCurrencies($store_id),
                'fields' => $tag_sets_and_custom_fields['custom_fields'],
                'tag_sets' => $tag_sets_and_custom_fields['tag_sets'],
                'sort_options' =>  $this->getSortOptions(),
                'timezone' => Mage::getStoreConfig('general/locale/timezone', $store_id),
                'products_count' => Mage::helper('tagalys_core/SyncFile')->getFeedCount($store_id, true)
            );
        }
        return $client_configuration;
    }

    public function getTagSetsAndCustomFields($store_id) {
        $tagalys_core_fields = array("__id", "name", "sku", "link", "sale_price", "image_url", "introduced_at", "in_stock");
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
        $tag_sets = array();
        $tag_sets[] = array("id" =>"__categories", "label" => "Categories", "filters" => true, "search" => true);
        $custom_fields = array();
        $magento_tagalys_type_mapping = array(
            'text' => 'string',
            'textarea' => 'string',
            'date' => 'datetime',
            'boolean' => 'boolean',
            'multiselect' => 'string',
            'select' => 'string',
            'price' => 'float'
        );
        foreach ($attributes as $attribute){
            if ($attribute->getIsFilterable() || $attribute->getIsSearchable()) {
                if ($attribute->getFrontendInput() == "select" || $attribute->getFrontendInput() == "multiselect") {
                    $tag_sets[] = array(
                        'id' => $attribute->getAttributecode(),
                        'label' => $attribute->getStoreLabel($store_id),
                        'filters' => (bool)$attribute->getIsFilterable(),
                        'search' => (bool)$attribute->getIsSearchable()
                    );
                } else if (!in_array($attribute->getAttributecode(), $tagalys_core_fields)) {
                    // custom field
                    $is_price_field = ($attribute->getFrontendInput() == "price" );
                    if (array_key_exists($attribute->getFrontendInput(), $magento_tagalys_type_mapping)) {
                        $type = $magento_tagalys_type_mapping[$attribute->getFrontendInput()];
                    } else {
                        $type = 'string';
                    }
                    $custom_fields[] = array(
                        'name' => $attribute->getAttributecode(),
                        'label' => $attribute->getStoreLabel($store_id),
                        'type' => $type,
                        'currency' => $is_price_field,
                        'display' => $is_price_field,
                        'filters' => (bool)$attribute->getIsFilterable(),
                        'search' => (bool)$attribute->getIsSearchable()
                    );
                }
            }
        }
        return compact('tag_sets', 'custom_fields');
    }

    public function getCurrencies($store_id) {
        $currencies = array();
        $codes = Mage::app()->getStore($store_id)->getAvailableCurrencyCodes();
        $rates = Mage::getModel('directory/currency')->getCurrencyRates(
            Mage::app()->getStore($store_id)->getBaseCurrency(),
            $codes
        );
        $baseCurrencyCode = Mage::app()->getStore($store_id)->getBaseCurrencyCode();
        if (empty($rates[$baseCurrencyCode])) {
            $rates[$baseCurrencyCode] = array($baseCurrencyCode => '1.0000');
        }
        foreach ($codes as $code) {
            if (isset($rates[$code])) {
                $defaultCurrency = ($baseCurrencyCode == $code ? true : false);
                $label = Mage::app()->getLocale()->currency($code)->getSymbol();
                if (empty($label)) {
                    $label = $code;
                }
                $currencies[] = array(
                    'id' => $code,
                    'label' => $label,
                    'exchange_rate' => $rates[$code],
                    'rounding_mode' => 'round',
                    'fractional_digits' => 2,
                    'default' => $defaultCurrency
                );
            }
        }
        return $currencies;
    }

    public function getSortOptions() {
        $sort_options = array();
        foreach (Mage::getResourceModel('catalog/config')->getAttributesUsedForSortBy() as $key => $value) {
            $sort_options[] = array(
                'field' => $value["attribute_code"],
                'label' => $value["store_label"]
            );
        }
        return $sort_options;
    }
}
