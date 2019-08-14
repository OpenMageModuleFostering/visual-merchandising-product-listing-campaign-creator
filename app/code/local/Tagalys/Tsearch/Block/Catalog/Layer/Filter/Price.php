<?php

if (Mage::helper('tsearch')->is_fme_active()) {
    class MiddleManBlockPriceClass extends FME_Layerednav_Block_Layer_Filter_Price { }
} else {
    class MiddleManBlockPriceClass extends Mage_Catalog_Block_Layer_Filter_Price { }
}

class Tagalys_Tsearch_Block_Catalog_Layer_Filter_Price extends Mage_Catalog_Block_Layer_Filter_Price 
{
    /**
     * Defines specific filter model name.
     *
     * @see Tagalys_Tsearch_Model_Catalog_Layer_Filter_Price
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'tsearch/catalog_layer_filter_price';
    }

    /**
     * Prepares filter model.
     *
     * @return Tagalys_Tsearch_Block_Catalog_Layer_Filter_Price
     */
    protected function _prepareFilter()
    {
        $this->_filter->setAttributeModel($this->getAttributeModel());

        return $this;
    }

    /**
     * Adds facet condition to filter.
     *
     * @see Tagalys_Tsearch_Model_Catalog_Layer_Filter_Price::addFacetCondition()
     * @return Tagalys_Tsearch_Block_Catalog_Layer_Filter_Price
     */
    public function addFacetCondition()
    {
        if (!$this->getRequest()->getParam('price')) {
            $this->_filter->addFacetCondition();
        }

        return $this;
    }

    public function setNewPrices() {
        $tagalys = Mage::helper('tsearch')->getTagalysSearchData();
        $filters = $tagalys["filters"];
        foreach ($filters as $filter) {

            if ($filter['prefix'] == 'price') {
              $filterType = $filter["type"];
              if($filterType == "range")
                $this->_minPrice = $filter["min"];
            $this->_maxPrice = $filter["max"];
        }
    }
    return $this;
}



}
