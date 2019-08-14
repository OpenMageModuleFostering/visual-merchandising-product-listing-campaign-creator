<?php
/**
 * Overrides default layer view process to define custom filter blocks.
 *
 * @package Tagalys_SearchSuggestions
 * @subpackage Tagalys_SearchSuggestions_Block
 * @author Tagalys
 */
class Tagalys_SearchSuggestions_Block_Catalog_Layer extends Mage_CatalogSearch_Block_Layer
{
    /**
     * Boolean block name.
     *
     * @var string
     */
    
    protected function _initBlocks()
    {
      parent::_initBlocks();
      $this->_categoryBlockName        = 'tagalys_ss/catalog_layer_filter_category';
      $this->_attributeFilterBlockName    = 'tagalys_ss/catalog_layer_filter_attribute';
    }

 }
