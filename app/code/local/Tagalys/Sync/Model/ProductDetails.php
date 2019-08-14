<?php
class Tagalys_Sync_Model_ProductDetails extends Mage_Core_Model_Abstract {
	
	protected $syncfield;
	protected $inventorySyncField;

	public function getProductFields($productId, $store = null) {
		try {
			$this->_storeId = $store;
			$sync_helper = Mage::helper('sync/data');
			$core_helper = Mage::helper('tagalys_core');
			$product = Mage::getModel('catalog/product')->load($productId);
			if(is_null($this->_storeId)) {
				$this->_storeId = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();	  	
			}
			Mage::app()->setCurrentStore($this->_storeId);
			$productFields = new stdClass();
			$attributes = $product->getAttributes();
			foreach ($attributes as $attribute) {
				if ($attribute->getIsFilterable() || $attribute->getIsSearchable()) {
					$attr = $product->getResource()->getAttribute($attribute->getAttributeCode());
					if (!$attr->usesSource()) {
						$field_val = $attribute->getFrontend()->getValue($product);
						if(!is_null($field_val)) {
							$productFields->{$attribute->getAttributeCode()} = $attribute->getFrontend()->getValue($product);
						}
					}
				}
			}
			$productFields->__id = $product->getId();
			$productFields->name = $product->getName();
			$productFields->link = $product->getProductUrl();
			$productFields->sku = $product->getData('sku');
			if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
				$arr = $product->getTypeInstance(true)->getChildrenIds($product->getId(), false);
				foreach($arr[key($arr)] as $k => $v ) {
					$price[] = Mage::getModel('catalog/product')->load($v)->getFinalPrice();
					$mrp[] = Mage::getModel('catalog/product')->load($v)->getPrice();
				}
				$productFields->sale_price = $this->getmultipleCurrency(min($price), true);
				$productFields->price = $this->getmultipleCurrency(min($mrp), true);
			} else {
				$productFields->sale_price = $this->getmultipleCurrency($product->getFinalPrice(), true);
				$productFields->price = $this->getmultipleCurrency($product->getPrice(), true);
				// $productFields->test_price = $product->getData('test_price');
			}

			$productFields->image_url = Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getImage());
			$fields = array( 'created_at');
			foreach ($fields as $key => $name) {

				$fieldValue = $product->getResource()->getAttribute($name)->getFrontend()->getValue($product);
				$utc = new DateTime((string)$fieldValue);
				$productFields->introduced_at = $utc->format(DateTime::ATOM);
			}

			// $productFields->parent_id = $this->getProductParent($productId);
			$productFields->in_stock = Mage::getModel('catalog/product')->load($product->getId())->isSaleable();

			return $productFields;

		} catch (Exception $e) {

		}		
	}
	public function getProductType($productId) {
		$product = Mage::getModel('catalog/product')->load($productId);
		$productType = $product->getTypeId();
		return $productType;
	}
	public function getProductParent($productId) {
		$tagalys_parent_id = array();
		$product = Mage::getModel('catalog/product')->load($productId);
		$parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($productId);
		if(!$parentIds) {
			$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);  
			$parentProducts = Mage::getModel('catalog/product')
			->getCollection()
			->addAttributeToSelect('sku')
			->addAttributeToFilter('entity_id', array('in' => $parentIds))
			->load();
			$parents = array();
			foreach ($parentProducts as $key => $pro) {
				array_push($parents, $pro->getId());
			}
			if(isset($parents[0])) {
				$tagalys_parent_id = $parents;
			} 
		} 
		return $tagalys_parent_id;
	}
	public function getProductAttributes($productId, $store_id, $unsyncFields)  {

		$product = Mage::getModel('catalog/product')->load($productId);
		$attribute_options_id = null;
		$attriute_option_value = null;
		$type = $product->getTypeId();
		$attributeObj = array();
		$product->setStoreId($store_id);
		$categories = Mage::helper('sync/data')->getProductTreeCat($productId);
		$attributeObj[] = array("tag_set" => array("id" => "__categories", "label" => "categories" ),"items" => ($categories));

		if(true) {
			$attributes = $product->getAttributes();

			foreach ($attributes as $attribute) {

				if ($attribute->getIsFilterable() || $attribute->getIsSearchable()) {

					$attr = $product->getResource()->getAttribute($attribute->getAttributeCode());
					if ($attr->usesSource()) {
						$attriute_option_value =$attribute->getFrontend()->getValue($product);
						$attribute_options_id = $attr->getSource()->getOptionId($attriute_option_value);
					} 
					$values['label'] = $attriute_option_value;
					$values['id'] = $attribute_options_id;
					if($values && !is_null($attribute_options_id) && $values['label'] != "N/A"){
						$attributeObj[] = array("tag_set" => array("id" => $attribute->getAttributeCode(), "label" => $attribute->getFrontend()->getLabel($product) ),"items" => array($values));
					}
				}
			}
		}
		if($type === "configurable") {
			$config = $product->getTypeInstance(true);
			foreach($config->getConfigurableAttributesAsArray($product) as $attributes)
			{
				$items = array();
				foreach($attributes["values"] as $val){
					$attr = $product->getResource()->getAttribute($attributes['attribute_code']);
					if ($attr->getIsFilterable() || $attr->getIsSearchable()) {
						$attriute_option_value = $val["label"];
						$attribute_options_id = $attr->getSource()->getOptionId($attriute_option_value);
					}
					$values['label'] = $attriute_option_value;
					$values['id'] = $attribute_options_id;
					$items[] = $values;
				}
				if(!empty($values) && $values['label'] != "N/A") {
					$attributeObj[] = array( "tag_set" => array("id" => $attributes['attribute_code'], "label" => $attributes["label"]),"items" => $items);
				}

			} 
		}

		return ($attributeObj);
	}
	public function getProductSyncField() {
		try {
			if(empty($this->syncfield)) {
				$attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
				$default_field = array();
				foreach ($attributes as $attribute) {
					$code = $attribute->getAttributecode();
    				//$label = $attribute->getFrontendLabel();
					if(isset($code)) {
						array_push($default_field, $code);
					}
				}
				$unsync_field = $this->_getProductUnSyncField();
				$this->syncfield = array_diff($default_field, $unsync_field);
				return $this->syncfield;	
			}
			return $this->syncfield;

		} catch (Exception $e) {

		}		
	}
	protected function _getProductUnSyncField() {
		$config = Mage::getStoreConfig('sync/product/sync_fields');
		$fields= explode(',',$config);
		return $fields;
	}
	public function getInventorySyncField() {
		try {
			if(empty($this->inventorySyncField)) {
				$helper = Mage::helper("sync/inventory");
				$stock_item = $helper->getInventoryStockItemField();
				$default_field = array();
				foreach ($stock_item as $key => $label) {
					if(isset($key)) {
						array_push($default_field, $key);
					}
				}
				$unsync_field = $this->_getInventoryUnSyncField();
				$this->inventorySyncField = array_diff($default_field, $unsync_field);
				return $this->inventorySyncField;	
			}
			return $this->inventorySyncField;
		} catch (Exception $e) {
		}
	}
	protected function _getInventoryUnSyncField() {
		$config = Mage::getStoreConfig('sync/inventory/inventory_fields');
		$fields= explode(',',$config);
		return $fields;
	}

//$rootCatId = Mage::app()->getStore()->getRootCategoryId();
	public function getFullCategoryTree($parentId, $isChild){
		$tree = array();
		$allCats = Mage::getModel('catalog/category')->getCollection()
		->addAttributeToSelect('*')
		->addAttributeToFilter('is_active','1')
		->addAttributeToFilter('include_in_menu','1')
		->addAttributeToFilter('parent_id',array('eq' => $parentId));


    //$children = Mage::getModel('catalog/category')->getCategories(7);
		foreach ($allCats as $category) 
		{
			array_push($tree, $category->getName());
			if($category->getChildren() != ''){
				array_push($tree, $this->getFullCategoryTree($category->getId(), true));
			}
		}
		return $tree;
	}

	// echo $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode(); get base currency for api requests
	public function getmultipleCurrency($price, $only_base = false) {
		$currencyModel = Mage::getModel('directory/currency');
		$currencies = $currencyModel->getConfigAllowCurrencies(); // abaliable currency
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode(); // default code
		$defaultCurrencies = $currencyModel->getConfigBaseCurrencies();
		$price_array = array();
		$rates=$currencyModel->getCurrencyRates($defaultCurrencies, $currencies); // rates of each currency
		if($only_base || empty($rates[$baseCurrencyCode])) {
			$price_array[$baseCurrencyCode] = 1 * $price;
		}
		if($only_base) {
			return floatval($price);
		}
		foreach($rates[$baseCurrencyCode] as $key=>$value  ) {
			$price_array[$key] = $value*$price; // getFinalPrice
		}
		return $price_array;
	}
}