<?php

if (Mage::helper('tsearch')->is_fme_active()) {
    class MiddleManBlockCategoryClass extends FME_Layerednav_Block_Layer_Filter_Category { }
} else {
    class MiddleManBlockCategoryClass extends Mage_Catalog_Block_Layer_Filter_Category { }
}

class Tagalys_Tsearch_Block_Catalog_Layer_Filter_Category extends MiddleManBlockCategoryClass
{
    /**
     * Defines specific filter model name.
     *
     * @see Tagalys_Tsearch_Model_Catalog_Layer_Filter_Category
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'tsearch/catalog_layer_filter_category';
    }

    /**
     * Adds facet condition to filter.
     *
     * @see Tagalys_Tsearch_Model_Catalog_Layer_Filter_Category::addFacetCondition()
     * @return Tagalys_Tsearch_Block_Catalog_Layer_Filter_Attribute
     */
    public function addFacetCondition()
    {
        $this->_filter->addFacetCondition();

        return $this;
    }

    
}
