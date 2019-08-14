<?php
/**
 * Handles attribute filtering in layered navigation.
 *
 * @package Tagalys_Merchandising
 * @subpackage Tagalys_Merchandising_Block
 * @author Aaditya
 */
class Tagalys_MerchandisingPage_Block_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Block_Layer_Filter_Attribute
{
    /**
     * Defines specific filter model name.
     *
     * @see Tagalys_Merchandising_Model_Catalog_Layer_Filter_Attribute
     */
    public function __construct()
    {
        
        parent::__construct();
        
        $this->_filterModelName = 'merchandisingpage/catalog_layer_filter_attribute';
    }

    /**
     * Prepares filter model.
     *
     * @return Tagalys_Merchandising_Block_Catalog_Layer_Filter_Attribute
     */
    protected function _prepareFilter()
    {
        $this->_filter->setAttributeModel($this->getAttributeModel());

        return $this;
    }

    /**
     * Adds facet condition to filter.
     *
     * @see Tagalys_Merchandising_Model_Catalog_Layer_Filter_Attribute::addFacetCondition()
     * @return Tagalys_Merchandising_Block_Catalog_Layer_Filter_Attribute
     */
    public function addFacetCondition()
    {
        // Mage::log("Tagalys_Merchandising_Block_Catalog_Layer_Filter_Attribute::addFacetCondition()",null,'debug.log');
        $this->_filter->addFacetCondition();

        return $this;
    }
}
