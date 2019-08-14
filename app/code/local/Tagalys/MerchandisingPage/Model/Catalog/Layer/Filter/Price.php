<?php

// if (Mage::Helper('tsearch')->is_fme_active()) {
//   class MiddleManModelPriceClass extends FME_Layerednav_Model_Layer_Filter_Price { }
// } else {
//   class MiddleManModelPriceClass extends Mage_Catalog_Model_Layer_Filter_Price { }
// }

class Tagalys_MerchandisingPage_Model_Catalog_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{
  const CACHE_TAG = 'MAXPRICE';

  const DELIMITER = '-';

    /**
     * Returns cache tag.
     *
     * @return string
     */
    public function getCacheTag()
    {
      return self::CACHE_TAG;
    }

    /**
     * Retrieves max price for ranges definition.
     *
     * @return float
     */
    public function getMaxPriceMod()
    {
      $priceStat =  Mage::getSingleton('tagalys_merchandisingpage/catalog_layer')->getProductCollection()->getStats('price');
      $productCollection = $this->getLayer()->getProductCollection();
      return isset($priceStat["max"])?(int)$priceStat["max"]:0;
    }



    /**
     * Retrieves min price for ranges definition.
     *
     * @return float
     */
    public function getMinPriceMod()
    {
      $priceStat =  Mage::getSingleton('tagalys_tsearch/catalog_layer')->getProductCollection()->getStats('price');
      $productCollection = $this->getLayer()->getProductCollection();
      return isset($priceStat["min"])?(int)$priceStat["min"]:0;
    }

    /**
     * Returns price field according to current customer group and website.
     *
     * @return string
     */
    protected function _getFilterField()
    {
      $websiteId = Mage::app()->getStore()->getWebsiteId();
      $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
      $priceField = 'price' ;

      return $priceField;
    }

    /**
     * Retrieves current items data.
     *
     * @return array
     */
    protected function _getItemsData()
    {
     if (Mage::app()->getStore()->getConfig(self::XML_PATH_RANGE_CALCULATION) == self::RANGE_CALCULATION_IMPROVED) {
       return $this->_getCalculatedItemsData();}
       if ($this->getInterval()) {
        return array();
      }

      $data = array();
        // $facets = $this->getLayer()->getFacetedData($this->_getFilterField()); ref addy
       $service = Mage::getSingleton("Tagalys_MerchandisingPage_Model_Client");
   
      $tagalys = Mage::helper('merchandisingpage')->getTagalysSearchData();
      $filters = $tagalys["filters"];
      foreach ($filters as $filter) {

        if ($filter['id'] == 'price' ) {
          $filterType = $filter["type"];
          foreach ($filter["items"] as $filterItem) {
            if ($filterItem["count"]) {
              $facets[] = $filterItem;
            }
          }
        }

      }
      
      if (!empty($facets)) {
        $i = 0;
        foreach ($facets as $key => $price) {
          $i++;
          
          preg_match('/^(\S*)-(\S*)$/', $price["id"], $rangeKey);
           $fromPrice = $rangeKey[1];
           $toPrice = $rangeKey[2];
          if($filterType == "checkbox"){
            
            $data[] = array(
              // 'label' => $this->_renderRangeLabel($fromPrice, $toPrice),
            'label' => $price["name"],
              'value' => $price["id"],
              'count' => $price["count"]
              );
          } 
          
        }
      } elseif ($filterType == "range") {
       $data[] = array(
        'label' => $this->_renderRangeLabel($fromPrice, $toPrice),
        'value' => 10,200,
        'count' => $price["count"]
        );
     }
     return $data;
   }

  
    /**
     * Adds facet condition to product collection.
     *
     * @see Tagalys_Tsearch_Model_Resource_Catalog_Product_Collection::addFacetCondition()
     * @return Tagalys_Tsearch_Model_Catalog_Layer_Filter_Attribute
     */
    public function addFacetCondition()
    {
      $this->getLayer()
            // ->getProductCollection()
      ->addFacetCondition($this->_getFilterField());

      return $this;
    }


    // public function apply(Zend_Controller_Request_Abstract $request, $filterBlock){

    //   $filter = $request->getParam($this->_requestVar);
    //   if(null == $filter){
    //     return $this;
    //   }
    //   $filter =explode(self::DELIMITER, $filter);
    //   if (!is_array($filter) || null === $filter || sizeof($filter)<2 ) {
    //     return $this;
    //   }
    //   $this->applyFilterToCollection($this, $filter);
    //   $this->_items = null;
    //   return $this;
    // }


    function applyFilterToCollection($filter,$filterValue){
      $field = $this->_getFilterField();
      $value = array(
        $field => array(
          'include_upper' => 0
          )
        );

      if($filterValue[0]< $filterValue[1]){
        $value[$field]['from'] = $filterValue[0];
        $value[$field]['to'] = $filterValue[1];
      }else{
        $value[$field]['from'] = $filterValue[1];
        $value[$field]['to'] = $filterValue[0];
      }
      $this->getLayer()->getProductCollection();
      return $this;
    }
  }