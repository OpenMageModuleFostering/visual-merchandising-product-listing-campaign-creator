<?php
class Tagalys_Sync_Helper_Service extends Mage_Core_Helper_Abstract {
	protected $_storeId;
	public function productSyncBySoap($page, $limit) {
    $payload = array();
		try {
			if(empty($this->_storeId)) {
				$this->_storeId = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();	
			} 
			
			Mage::app()->setCurrentStore($this->_storeId);
			$collection = Mage::getResourceModel('catalog/product_collection')
      //->getCollection()
			->setStore($this->_storeId)
			->setPageSize($limit)
			->addAttributeToSelect('*')
			->setCurPage($page);
			
			$payload = $this->getProductWithParentAttribute($collection,$store_id);
			return $payload;
			
		} catch (Exception $e) {
			
		}
	}
	
	public function getProductWithParentAttribute($products,$store_id) {
		$payload = array();
		foreach ($products as $product) {
			$simpleProduct = $this->getSingleProductWithoutPayload($product->getId(),$store_id);
			if(isset($simpleProduct)) {
				array_push($payload,array("perform" => "index", "payload" => $simpleProduct));
			}		
		}
		return $payload;
	}


	public function getSingleProductWithoutPayload($product_id, $store_id, $admin = false) {

    $core_helper = Mage::helper('tagalys_core');
    $sync_helper = Mage::helper('sync/data');
    $sync_level = $core_helper->getTagalysConfig("sync_level");
    $details_model = Mage::getModel("sync/productDetails");

    $product_data = new stdClass();
    $utc_now = new DateTime((string)date("Y-m-d h:i:s"));
    $time_now =  $utc_now->format(DateTime::ATOM);
    $attr_data = array();
    $attributes = $details_model->getProductAttributes($product_id, $store_id, array_keys((array) $product_data));
    $product_data = $details_model->getProductFields($product_id, $store_id);
    $product_data->synced_at = $time_now;
    $product_data->__tags = $attributes;
    if($sync_level == "advanced"){
      $product_data->raw_source = $this->getRawData($product_id);
    }

    // $product_data->tagalys_product_type = $details_model->getProductType($product_id);

    return $product_data;
  }
  public function getProductTotal() {
    try {
      $record = Mage::getModel('catalog/product')->getCollection()->count();
      return $record;
    } catch (Exception $e) {
    }
  }
  public function getQueueSize() {
    try {
      $record = Mage::getModel('sync/queue')->getCollection()->count();
      return $record;
    } catch (Exception $e) {
    }
  }
  public function getSingleProductBySku($sku) {
    try {
      if(isset($sku)) {
        if(empty($this->_storeId)) {
          $this->_storeId = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();	
        } 
        Mage::app()->setCurrentStore($this->_storeId);
        $collection = Mage::getResourceModel('catalog/product_collection')
        ->setStore($this->_storeId)
        ->addAttributeToSelect('*')
        ->addFilter('sku',$sku);
        $product = $this->getSingleProductWithoutPayload($collection->getFirstItem());
        return $product;
      }
      return null;
    } catch (Exception $e) {
    }
  }
  public function getProductUpdate($limit, $store) {
    try {
      $this->_storeId = $store;
      $collection = Mage::getModel('sync/queue')->getCollection()->setOrder('id', 'DESC');
      $collection->getSelect()->limit($limit);
      $products_id = array(); 
      $deleted_ids = array(); 
      $existing_products_id = array();
      $non_existing_ids = array();
      $count = 0;
      foreach ($collection as $key => $queue) {
        $product_id = $queue->getData('product_id');
        if (is_numeric($product_id)) {
          array_push($products_id, $product_id);
        } else {
          array_push($deleted_ids, $product_id);
        }
      }

      $productCollection = Mage::getModel('catalog/product')->getCollection()
      ->addAttributeToSelect('*')
      ->addAttributeToFilter('status',1) //only enabled product 
      ->addAttributeToFilter('visibility',array("neq"=>1)) //except not visible individually 
      ->setStoreId($this->_storeId)
      ->addStoreFilter($this->_storeId)
      ->addAttributeToFilter( 'entity_id', array( 'in' => $products_id ));

      $respone = $this->getProductWithParentAttribute($productCollection, $this->_storeId);
      foreach ($respone as $key => $value) {
        array_push($existing_products_id, $value["payload"]->__id);
        $count++;
      }
      $non_existing_ids =  array_merge(array_diff($existing_products_id, $products_id), array_diff($products_id, $existing_products_id));
      foreach ($non_existing_ids as $key => $value) {
        if(isset($value)) {
          $deleted = new stdClass;
          $deleted->perform = "delete";
          $deleted->payload->__id = $value;
          // $deleted->deleted = true ;
          array_push($respone, $deleted);
        }
        
      }
      foreach ($deleted_ids as $key => $value) {
        $deleted = new stdClass;
        $deleted->perform = "delete";
       $temp= explode("sku-", $value);
        $sku = $temp[1];
        if(!empty($sku)){
          $deleted->payload->sku = $sku;
        }
        // $deleted->deleted =  true ;
        array_push($respone, $deleted);
      }
      
      foreach ($collection as $key => $queue) {
        $queue->delete();
      }
      return $respone;
    } catch (Exception $e) {
    }
  }
  public function getRawData($product_id) {

    if(empty($this->_storeId)) {
      $this->_storeId = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();      
      Mage::app()->setCurrentStore($this->_storeId);
    }
    $syncField = Mage::getModel("sync/productDetails");
    $fields = $syncField->getProductSyncField();
       //Add Extra field
    $fields = array_merge($fields, array('created_at', 'updated_at'));
    $productObject = new stdClass();
    $product = Mage::getModel('catalog/product')->load($product_id);
    $productObject->product_id = $product_id;
       //Get Category Details
    $category = $product->getCategoryIds();
    $categoryList = array();
    foreach ($category as $category_id) {
      $_cat = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($category_id);
      $categoryList[] = $_cat->getName();             
    }
       //Category Container
    $productObject->categories = $categoryList;
       //Get Tag Details
    $model=Mage::getModel('tag/tag');
    $tagsOption = $model->getResourceCollection()
    ->addPopularity()
    ->addStatusFilter($model->getApprovedStatus())
    ->addProductFilter($product->getId())
    ->setFlag('relation', true)
    ->addStoreFilter($this->_storeId)
    ->setActiveFilter()
    ->load();
    $tags = array();
    foreach($tagsOption as $tag) $tags[] = $tag->getName();
    $productObject->tags = $tags;
        //Field Container
    if(empty($fields)) {
      $productObject->sku = $product->getData('sku');
      $productObject->price = $product->getData('cost');
    } 
    else {
      foreach ($fields as $key => $name) {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $name);
        if ($attribute->usesSource()) {
          $attributeCode = $attribute->setStoreId($this->_storeId)->getAttributeCode();
          $fieldValue = $product->getResource()->getAttribute($attributeCode)->getFrontend()->getValue($product);
          $productObject->{$name} = $fieldValue;//$product->getAttributeText($name);   
        } else {
          $filter = array('image','small_image','thumbnail');
          if(in_array($name, $filter)) {
            $productObject->{$name} = '/media/catalog/product' . $product->getImage();
            try {
              if($name == "thumbnail") {
                $productObject->tagalys_thumbnail_url = (string)Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(170);
              } 
            } catch(Exception $e) {
              $productObject->tagalys_thumbnail_url = null;
          // Mage::log("Product Image not found", null, "tagalys.log");
            }
          } 
          else if($name == 'url_key') {
            $productObject->{$name} = $product->getData($name); 
            $productObject->url = parse_url($product->getProductUrl(), PHP_URL_PATH);   
          }
          else {
            $productObject->{$name} = $product->getData($name); 
          }
        }
      }
          //Adding stock field
      $stockField = $syncField->getInventorySyncField();
      $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
      foreach ($stockField as $key => $field) {
        $productObject->{$field} = $stock->getData($field); 
      }
    }
        //Add product type field for identification
    $productType = $product->getTypeId();
    $productObject->tagalys_product_type = $productType;
    $details_model = Mage::getModel("sync/productDetails");
    $product_data->tagalys_parent_id = $details_model->getProductParent($product_id);

    return $productObject;
  }  

  public function getClientCurrencyData() {
    $currency_rate = array();
    $currencyModel = Mage::getModel('directory/currency');
    $currencies = $currencyModel->getConfigAllowCurrencies(); // abaliable currency
    $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode(); //default code
    $defaultCurrencies = $currencyModel->getConfigBaseCurrencies();
    $rates=$currencyModel->getCurrencyRates($defaultCurrencies, $currencies); //rates of each currency

    foreach($rates[$baseCurrencyCode] as $key=>$value  ) {
      $default = $baseCurrencyCode == $key ? true : false;
      $label = Mage::app()->getLocale()->currency( $key )->getSymbol();
      $currency_rate[] = array("id" => $key, "label" => $label, "fractional_digits" => 2 , "rounding_mode" => "round", "exchange_rate" => (float)$value, "default" => $default); //getFinalPrice
    }

    return $currency_rate;
  }

  public function getClientSortOptions() {
    $sort_options = array();
    foreach (Mage::getResourceModel('catalog/config')->getAttributesUsedForSortBy() as $key => $value) {
      # code...
      $sort_options[] = array("field" => $value["attribute_code"], "label" => $value["store_label"]);
    }
    return $sort_options;
  }

  public function getClientSetData() {
    $diff = array("name","sku","__id","link","sale_price","image_url","introduced_at","in_stock");
    $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
    $attributes_tag_set = array();
    $attributes_fields = array();
    $types = Mage::getModel('eav/adminhtml_system_config_source_inputtype')->toOptionArray();
    array_push($types, array("value" => "price"));
    foreach ($types as $key => $value) {
      if($value["value"] == "price") {
        $typemap[$value["value"]] = "float";
      } elseif ($value["value"] == "boolean") {
        $typemap[$value["value"]] = "boolean";
      } elseif ($value["value"] == "date") {
        $typemap[$value["value"]] = "date";
      } else {
        $typemap[$value["value"]] = "string";
      }
      
    }
    foreach ($attributes as $attribute){
      if(($attribute->getIsFilterable() || $attribute->getIsSearchable()) && ($attribute->getFrontendInput() == "select" || $attribute->getFrontendInput() == "multiselect" )) {
        $attributes_tag_set[] = array("id" =>$attribute->getAttributecode(), "label" =>$attribute->getFrontendLabel(), "filters" => (bool)$attribute->getIsFilterable(), "search" => (bool)$attribute->getIsSearchable());
      } else if(($attribute->getIsFilterable() || $attribute->getIsSearchable()) && !in_array($attribute->getAttributecode(), $diff)) {
        if ($attribute->getFrontendInput() == "price" ) {
          $attributes_fields[] = array("name" =>$attribute->getAttributecode(), "label" =>$attribute->getFrontendLabel(),"type" => $typemap[$attribute->getFrontendInput()],"currency" => true, "display" => true, "filters" => (bool)$attribute->getIsFilterable(),"search" => (bool)$attribute->getIsSearchable());
        } else {
         $attributes_fields[] = array("name" =>$attribute->getAttributecode(), "label" =>$attribute->getFrontendLabel(),"type" => $typemap[$attribute->getFrontendInput()], "filters" => (bool)$attribute->getIsFilterable(),"search" => (bool)$attribute->getIsSearchable());
       }
       
     }

   }
   $attributes_tag_set[] = array("id" =>"__categories", "label" =>"categories", "filters" => (bool)true, "search" => (bool)true);
   return array("tag_set" => $attributes_tag_set, "fields" => $attributes_fields );
 }
}
