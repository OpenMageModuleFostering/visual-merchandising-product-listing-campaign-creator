<?php
/**
  * Handles attribute filtering in layered navigation in a query search context.
 *
 * @package Unbxd_Search
 * @subpackage Unbxd_Search_Block
 * @author Tagalys
 */
class Tagalys_Tsearch_Block_Catalogsearch_Layer_Filter_Attribute extends Mage_Catalog_Block_Layer_Filter_Attribute
{
    /**
     * Set filter model name
     *
     */
    public function __construct()
    {
    	parent::__construct();
    	$this->_filterModelName = 'tsearch/catalogsearch_layer_filter_attribute';

    }
    protected function _prepareFilter()
    {
    	$this->_filter->setAttributeModel($this->getAttributeModel());

    	return $this;
    }

    /**
     * Adds facet condition to filter.
     *
     * @see Tagalys_Tsearch_Model_Catalog_Layer_Filter_Attribute::addFacetCondition()
     * @return Tagalys_Tsearch_Block_Catalogsearch_Layer_Filter_Attribute
     */
    public function addFacetCondition()
    {
    	$this->_filter->addFacetCondition();
    	return $this;
    }
  }
