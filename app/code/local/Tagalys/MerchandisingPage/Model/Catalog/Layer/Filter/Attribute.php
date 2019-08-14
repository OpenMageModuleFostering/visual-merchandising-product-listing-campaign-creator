  <?php

  class Tagalys_MerchandisingPage_Model_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute {
   const OPTIONS_ONLY_WITH_RESULTS = 1;
   const MULTI_SELECT_FACET_SPLIT = '-';

   public function addFacetCondition() {

    $this->getLayer()
      // ->getProductCollection()
    ->addFacetCondition($this->_getFilterField());
    return $this;
  }

  public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
  {
    $filter = $request->getParam($this->_requestVar);
        //aadi

    $request = Mage::app()->getRequest()->getParams();
    if (!empty($request["qf"])) {
      foreach (explode("~", $request["qf"]) as $key => $value) {

        $temp = explode("-", $value);
        if($temp[0] == $this->_requestVar) {

          $temp_val = explode("-", $value);
          $filter = $temp_val[1];

        }
                # code...
      }
    }
        //aadi end
    if (is_array($filter)) {
      return $this;
    }
    $text = $this->_getOptionText($filter);
    if ($filter && strlen($text)) {
      $this->_getResource()->applyFilterToCollection($this, $filter);
      $this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));
      $this->_items = array();
    }
    return $this;
  }

  public function applyFilterToCollection($filter, $value) {
    if(!is_array($value)) {
      return $this;
    }
    $attribute = $filter->getAttributeModel();
    $param = Mage::helper('merchandisingpage')->getSearchParam($attribute, $value);

    $this->getLayer()
    ->getProductCollection();
    return $this;
  }

    /**
     * Returns facets data of current attribute.
     *
     * @return array
     */
    protected function _getFacets() {
      /** @var $productCollection Tagalys_Tsearch_Model_Resource_Catalog_Product_Collection */
      $productCollection = $this->getLayer()->getProductCollection();
      $fieldName = $this->_getFilterField();
      $facets = $productCollection;
      return $facets;
    }


    public function getMaxPriceInt() {
      $priceStat =  Mage::getSingleton('tagalys_merchandisingpage/catalog_layer')->getProductCollection()->getStats('price');
      $productCollection = $this->getLayer()->getProductCollection();
      return isset($priceStat["max"])?$priceStat["max"]:0;
    }


    /**
     * Returns attribute field name.
     *
     * @return string
     */
    protected function _getFilterField() {

      /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
      // Mage::log("Tagalys_Tsearch_Model_Catalog_Layer_Filter_Attribute::_getFilterField()",null,'debug.log');
      $attribute = $this->getAttributeModel();
      $fieldName = Mage::helper('merchandisingpage')->getAttributeFieldName($attribute);

      return $fieldName;
    }

    /**
     * Retrieves current items data.
     *
     * @return array
     */

    protected function _getItemsData() {
      $attribute = $this->getAttributeModel();
      $this->_requestVar = $attribute->getAttributeCode();
    
      $key = $this->getLayer()->getStateKey().'_'.$this->_requestVar;
      $data = $this->getLayer()->getAggregator()->getCacheData($key);

      if ($data === null) {
      $filters = $attribute->getFrontend()->getSelectOptions();
          $service = Mage::getSingleton("Tagalys_MerchandisingPage_Model_Client");
   
       $tagalys = Mage::helper('merchandisingpage')->getTagalysSearchData();
        // $service = Mage::getModel("Tagalys_MerchandisingPage_Model_Client");
        // $tagalys = $service->merchandisingPage(array());
       // die(var_dump($tagalys));
       $filters = $tagalys["filters"];
       $optionsCount = count($filters);
       $data = array();
       foreach ($filters as $filter) {
        if (!empty($filter['items'])) {
        // Check filter type
          if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS && $this->_requestVar == $filter['id'] ) {
            foreach ($filter["items"] as $filterItem) {
              if ($filterItem["count"]) {
                $data[] = array(
                                'label' => $filterItem["name"],
                    'value' =>  $filterItem["id"], //$filterItem["id"],
                    'count' => $filterItem["count"],
                    );
              }
            }
          }
        }
      }

      $tags = array(
                    Mage_Eav_Model_Entity_Attribute::CACHE_TAG.':'.$attribute->getId()
                    );

      $tags = $this->getLayer()->getStateTags($tags);
      $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
    }
    return $data;
  }

  protected function _getOptionText($optionId) {
    if ($this->getAttributeModel()->getFrontendInput() == 'text') {
      return $optionId;  
    }

    return parent::_getOptionText($optionId);
  }

  protected function getLabel($optionId) {
    if ($this->getAttributeModel()->getFrontendInput() == 'text') {
      return $optionId;  
    }

    return parent::_getOptionText($optionId);
  }

  protected function _isValidFilter($filter) {
    return !empty($filter);
  }

}