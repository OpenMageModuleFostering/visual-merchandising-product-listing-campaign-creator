<?php
/**
 * Handles decimal attribute filtering in layered navigation.
 *
 * @package Tagalys_MerchandisingPage
 * @subpackage Tagalys_MerchandisingPage_Block
 * @author Tagalys
 */
class Tagalys_MerchandisingPage_Block_Catalog_Layer_Filter_Price extends Mage_Catalog_Block_Layer_Filter_Price
{
    /**
     * Defines specific filter model name.
     *
     * @see Tagalys_MerchandisingPage_Model_Catalog_Layer_Filter_Price
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'merchandisingpage/catalog_layer_filter_price';
    }

    /**
     * Prepares filter model.
     *
     * @return Tagalys_MerchandisingPage_Block_Catalog_Layer_Filter_Price
     */
    protected function _prepareFilter()
    {
        $this->_filter->setAttributeModel($this->getAttributeModel());

        return $this;
    }

    /**
     * Adds facet condition to filter.
     *
     * @see Tagalys_MerchandisingPage_Model_Catalog_Layer_Filter_Price::addFacetCondition()
     * @return Tagalys_MerchandisingPage_Block_Catalog_Layer_Filter_Price
     */
    public function addFacetCondition()
    {
        if (!$this->getRequest()->getParam('price')) {
            $this->_filter->addFacetCondition();
        }

        return $this;
    }
}