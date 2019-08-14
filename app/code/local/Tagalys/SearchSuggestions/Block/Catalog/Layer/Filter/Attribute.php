<?php

class Tagalys_SearchSuggestions_Block_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Block_Layer_Filter_Attribute
{
    /**
     * Defines specific filter model name.
     *
     * @see Tagalys_SearchSuggestions_Model_Catalog_Layer_Filter_Attribute
     */
    public function __construct()
    {
      parent::__construct();
      
      $this->_filterModelName = 'tagalys_ss/catalog_layer_filter_attribute';
    }
   
  }
