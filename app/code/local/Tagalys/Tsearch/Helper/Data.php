<?php
class Tagalys_Tsearch_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getTagalysSearchData() {
    $controllerModule = Mage::app()->getRequest()->getControllerModule();
    if($controllerModule == 'Tagalys_MerchandisingPage') {
      $service = Mage::getSingleton("merchandisingpage/client");
    } else {
      $service = Mage::getSingleton("tsearch/client_connector");
    }
    // $service = Mage::getSingleton("tsearch/client_connector");
		if($this->isTagalysActive()) {
			$searchResult = $service->getSearchResult();
			if ($searchResult == null) {
				return false;
			} else {
				return $searchResult;
			}
		} else {
			return false;
		}
	}

	public function isTagalysActive() {
    
    $status =  Mage::helper('tagalys_core')->getTagalysConfig("is_tsearch_active");
    $query = Mage::app()->getRequest()->getParam('q');

    if ($status && !empty($query)) {
      $service = Mage::getSingleton("tsearch/client_connector");
      $tagalys = $service->isRequestSuccess();
      if($tagalys) {
        return $service;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  public function getTagalysFilter() {
    $result =  Mage::helper('tsearch')->getTagalysSearchData();
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


  public function is_fme_active() {
    return Mage::helper('core')->isModuleEnabled('FME_Layerednav');
  }

  public function dynamic_class_name() {

    if(Mage::helper('core')->isModuleEnabled('FME_Layerednav')) {
      return "FME_Layerednav";
    } elseif (Mage::helper('core')->isModuleEnabled('Magehouse_Slider')) {
      return "Magehouse_Slider";
    }
    return "Mage_Catalog";
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



