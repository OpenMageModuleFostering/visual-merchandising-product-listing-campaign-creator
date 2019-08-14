<?php
/**
 * Merchandising Page Helper 
 */
class Tagalys_MerchandisingPage_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function getMerchandisingData() {
      $service = Mage::getModel("Tagalys_MerchandisingPage_Model_Client");
      // die(var_dump($service));
      $query = Mage::app()->getRequest()->getParam('q');
      $q = Mage::app()->getRequest();
  
      $request =  array();
      $request = $q->getParams();
      $request['filters'] = true;
      $request['q'] = $request['product'];
  
      $payload = $request; 
      $entity = 'catalog_product';

      $current_list_mode = Mage::app()->getLayout()->createBlock('catalog/product_list_toolbar')->setChild('product_list_toolbar_pager', $pager)->getCurrentMode();
  
      if( $current_list_mode == "grid" || $current_list_mode == "grid-list") {
        $defaultLimit = Mage::getStoreConfig('catalog/frontend/grid_per_page');
        
      } else if($current_list_mode == "list" || $current_list_mode == "list-grid") {
        $defaultLimit = Mage::getStoreConfig('catalog/frontend/list_per_page');
      }

      $payload['filters'] = true;

      $session_limit = $request['limit']; //Mage::getSingleton('catalog/session')->getLimitPage();

      $payload['per_page'] = (!empty($session_limit) ? $session_limit : $defaultLimit);
      $payload['page'] = (!empty($request['p'])) ? $request['p'] : 1;
      // $payload['per_page'] = (!empty($session_limit) ? $session_limit : $defaultLimit) *  ($payload['page'] + 1);
      //  if($payload['page'] == 1) {
      //   $payload['per_page'] = (!empty($session_limit) ? $session_limit : $defaultLimit) * 2;
      //  } else {
      // $payload['per_page'] = (!empty($session_limit) ? $session_limit : $defaultLimit) ;
      //  }
      foreach ($request as $key => $value) {
        
        $code = $key;
        $attr = Mage::getResourceModel('catalog/eav_attribute')
        ->loadByCode($entity,$code);

        if ($attr->getId()) {
          $filters[$key] = array($request[$key]);
        }
        
      }

      if(isset($request["cat"])) {
        $filters["__categories"] = array($request["cat"]);
        // $payload["f"] = $category;
      }
      if(isset($request["min"]) && isset($request["max"])) {
        $filters["price"] = array("min" => $request["min"], "max" => $request["max"] );
      }
      if(!empty($filters)) {
        $payload["f"] = ($filters);
      }

      //$payload['filters'] = true;
      $payload['request'] = array("variables","url_component","results","sort_options","filters","total");
      $payload["store"] = Mage::app()->getStore()->getStoreId();
      
      //by aaditya 
     if(isset($request['order'])) {
        $payload['sort'] = $request['order']."-".$request['dir'];
        // $payload['order'] = $request['dir'];
      } else {
        $payload['sort'] = $payload['sort']; //Mage::getSingleton('catalog/session')->getSortOrder();
      }
      $user_id = "";
      $request["seed"] = "";
      if (Mage::getSingleton('customer/session')->isLoggedIn()) {
        $customer = Mage::getSingleton('customer/session')->getCustomer(); 
        $user_id = $customer->getID();
      } 

      $device_id = Mage::getModel('core/cookie')->get('__ta_device');
      $visitor_id = Mage::getModel('core/cookie')->get('__ta_visit');

      if (isset($payload)) {
        $payload['user'] = (array("ip" => Mage::helper('core/http')->getRemoteAddr(), "snapshot" => $request["snapshot"] , "visitor_id" => $visitor_id, "user_id" => $user_id , "device_id" => $device_id));
      }

      unset($payload['isAjax']);
   
      unset($payload['limit']);
      unset($payload['product']);
      unset($payload['dir']);
      unset($payload['order']);
      

      return $service->merchandisingPage($payload);
  }


  public function getTagalysSearchData() {
    $controllerModule = Mage::app()->getRequest()->getControllerModule();
    if($controllerModule == 'Tagalys_MerchandisingPage') {
      $service = Mage::getSingleton("merchandisingpage/client");
    } else {
      $service = Mage::getSingleton("tsearch/client_connector");
    }
    if($this->isTagalysActive()) {
        $searchResult = $service->getSearchResult();
	// die(var_dump($searchResult));
        if ($searchResult == null || ( isset($searchResult["status"]) && $searchResult["status"] != "OK")) {
            return false;
        } else {

            return $searchResult;
        }
    } else {
        return false;
    }

  }

  public function isTagalysActive() {
    
    $status = Mage::helper('tagalys_core')->getTagalysConfig("is_merchandising_page_active");

    if ($status) {
      $service = Mage::getSingleton("merchandisingpage/client");
      // $tagalys = $service->isRequestSuccess();
      if($service) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
    // return true;
  }

  public function getTagalysFilter() {
    $result =  Mage::helper('merchandisingpage')->getTagalysSearchData();
    if ($result) {
     $data = $result;
     $filters = (!empty($data['filters'])) ? $data['filters'] : null ;
     return $filters;
    }
    return false;
  }

  public function getAttributeFieldName($attribute, $localeCode = null)
  {
   // Mage::log($attribute,null,'debug.log');

   if (is_string($attribute)) {
       $this->getSearchableAttributes(); // populate searchable attributes if not already set
       if (!isset($this->_searchableAttributes[$attribute])) {
          return $attribute;
       }
       $attribute = $this->_searchableAttributes[$attribute];
   }
     $attributeCode = $attribute->getAttributeCode();
     $backendType = $attribute->getBackendType();

     return $attributeCode;
  }

  public function getSearchParam($attribute, $value)
    {
      if (empty($value) ||
        (isset($value['from']) && empty($value['from']) &&
          isset($value['to']) && empty($value['to']))) {
        return false;
    }

    $field = $this->getAttributeFieldName($attribute);
    $backendType = $attribute->getBackendType();
    if ($backendType == 'datetime') {
      $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
      if (is_array($value)) {
        foreach ($value as &$val) {
          if (!is_empty_date($val)) {
            $date = new Zend_Date($val, $format);
            $val = $date->toString(Zend_Date::ISO_8601) . 'Z';
          }
        }
        unset($val);
      } else {
        if (!is_empty_date($value)) {
          $date = new Zend_Date($value, $format);
          $value = $date->toString(Zend_Date::ISO_8601) . 'Z';
        }
      }
    }

    if ($attribute->usesSource()) {
      $attribute->setStoreId(Mage::app()->getStore()->getId());
    }

    return array($field => $value);
  }
}
