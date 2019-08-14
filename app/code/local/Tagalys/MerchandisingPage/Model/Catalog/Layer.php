<?php

class Tagalys_MerchandisingPage_Model_Catalog_Layer extends Mage_CatalogSearch_Model_Layer
{

protected $_facetsConditions = array();
 
  public function getProductCollection()
  {
    try {

          // $service = Mage::getSingleton("Tagalys_MerchandisingPage_Model_Client");
   
      $tagalysSearchResults = Mage::helper('merchandisingpage')->getTagalysSearchData();
          
      if($tagalysSearchResults == false) {
        
        return parent::getProductCollection();
      } else {
        if(empty($tagalysSearchResults)) {
          // die('tagalysSearchResults is empty!');
          return parent::getProductCollection();
        }
  
        $collection = $this->_productCollection = Mage::getModel('catalog/product')
        ->getCollection()
        ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
        ->setStore(Mage::app()->getStore())
        ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
        ->addAttributeToFilter( 'entity_id', array( 'in' => $tagalysSearchResults['results'] ) );

        $orderString = array('CASE e.entity_id');

        foreach($tagalysSearchResults['results'] as $i => $productId) {
          $orderString[] = 'WHEN '.$productId.' THEN '.$i;
        }
        $orderString[] = 'END';
        $orderString = implode(' ', $orderString);
        
        $collection->getSelect()->order(new Zend_Db_Expr($orderString));
        // die(var_dump($this->_productCollection));
        return $this->_productCollection;
 
      }
    } catch(Exception $e) {
      // (var_dump($e));
      return parent::getProductCollection();
    }
  }

   public function addFacetCondition($field, $condition = null)
    {
      
        if (array_key_exists($field, $this->_facetsConditions)) {
            if (!empty($this->_facetsConditions[$field])){
                $this->_facetsConditions[$field] = array($this->_facetsConditions[$field]);
            }
            $this->_facetsConditions[$field][] = $condition;
        } else {
            $this->_facetsConditions[$field] = $condition;
        }

        return $this;
    }

}
