<?php
	class Tagalys_Tsearch_Model_Observer  {

		public function createSearchResultPage(Varien_Event_Observer $observer) {
      if(Mage::helper('tagalys_core')->getTagalysConfig('is_tsearch_active')) {
        return Mage::getModel("tsearch/engine")->getCatalogSearchResult();
      }
		}
		
	}
