<?php

 
class Tagalys_Tsearch_Block_Catalogsearch_Result extends Mage_CatalogSearch_Block_Result
{   
  
    public function setListCollection()
    {
        $this->getListBlock()
           ->setCollection($this->_getProductCollection());
       return $this;
    }
    /**
     * Retrieve loaded category collection
     *
     * @return Mage_CatalogSearch_Model_Mysql4_Fulltext_Collection
     */
    protected function _getProductCollection()
    {

        if (is_null($this->_productCollection)) {
            //$this->_productCollection = $this->getListBlock()->getLoadedProductCollection();
            $this->_productCollection = Mage::getSingleton('catalogsearch/layer')->getProductCollection();
        }
        
        return $this->_productCollection;
    }
	
} 