<?php
class Tagalys_Tsearch_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List
{
	
	protected function _getProductCollection()
	{
		try {
			$tagalys = Mage::helper("tsearch")->getTagalysSearchData();
			
			if($tagalys == false) {
				
				return parent::_getProductCollection();
			} else {
				$searchResult = $tagalys;
				if(empty($searchResult)) {
					return parent::_getProductCollection();
				}
				
				$collection = $this->_productCollection = Mage::getModel('catalog/product')
				->getCollection()
				->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
				->setStore(Mage::app()->getStore())
				->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
				->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
				->addAttributeToFilter( 'entity_id', array( 'in' => $searchResult['results']) );
				
				$orderString = array('CASE e.entity_id');
				
				foreach($searchResult['results'] as $i => $productId) {
					$orderString[] = 'WHEN '.$productId.' THEN '.$i;
				}
				$orderString[] = 'END';
				$orderString = implode(' ', $orderString);
				$collection->getSelect()->order(new Zend_Db_Expr($orderString));
				return $this->_productCollection;
			}
		} catch(Exception $e) {
			return parent::_getProductCollection();
		}
	}

}