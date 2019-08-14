<?php
/**
 * Handles category filtering in layered navigation.
 *
 * @package Tagalys_MerchandisingPage
 * @subpackage Tagalys_MerchandisingPage_Block
 * @author Tagalys
 */
class Tagalys_MerchandisingPage_Block_Catalog_Layer_Filter_Category extends Mage_Catalog_Block_Layer_Filter_Category
{
    /**
     * Defines specific filter model name.
     *
     * @see Tagalys_MerchandisingPage_Model_Catalog_Layer_Filter_Category
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'merchandisingpage/catalog_layer_filter_category';
    }

    /**
     * Adds facet condition to filter.
     *
     * @see Tagalys_MerchandisingPage_Model_Catalog_Layer_Filter_Category::addFacetCondition()
     * @return Tagalys_MerchandisingPage_Block_Catalog_Layer_Filter_Attribute
     */
    public function addFacetCondition()
    {
        $this->_filter->addFacetCondition();

        return $this;
    }
}
