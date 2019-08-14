<?php

if (Mage::helper('tsearch')->is_fme_active()) {
    class MiddleManBlockAttributeClass extends FME_Layerednav_Block_Layer_Filter_Attribute { }
} else {
    class MiddleManBlockAttributeClass extends Mage_Catalog_Block_Layer_Filter_Attribute { }
}

class Tagalys_Tsearch_Block_Catalog_Layer_Filter_Attribute extends MiddleManBlockAttributeClass
{
    /**
     * Defines specific filter model name.
     *
     * @see Tagalys_Tsearch_Model_Catalog_Layer_Filter_Attribute
     */
    public function __construct()
    {
      parent::__construct();
      
      $this->_filterModelName = 'tsearch/catalog_layer_filter_attribute';
    }
    /**
     * Prepares filter model.
     *
     * @return Tagalys_Tsearch_Block_Catalog_Layer_Filter_Attribute
     */
    protected function _prepareFilter()
    {
      $this->_filter->setAttributeModel($this->getAttributeModel());
      return $this;
    }
    /**
     * Adds facet condition to filter.
     *
     * @see Tagalys_Tsearch_Model_Catalog_Layer_Filter_Attribute::addFacetCondition()
     * @return Tagalys_Tsearch_Block_Catalog_Layer_Filter_Attribute
     */
    public function addFacetCondition()
    {
      $this->_filter->addFacetCondition();
      return $this;
    }
  }
