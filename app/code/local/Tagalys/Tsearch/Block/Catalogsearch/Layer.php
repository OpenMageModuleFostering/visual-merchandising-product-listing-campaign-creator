

<?php

class Tagalys_Tsearch_Block_Catalogsearch_Layer extends Tagalys_Tsearch_Block_Catalogsearch_Layer_View {

 protected function _initBlocks()
 {
  parent::_initBlocks();
  
}
public function canShowBlock() {

  $availableResCount = (int) Mage::app()->getStore()
  ->getConfig(Mage_CatalogSearch_Model_Layer::XML_PATH_DISPLAY_LAYER_COUNT);

  if (!$availableResCount || ($availableResCount >= $this->getLayer()->getProductCollection()->getSize())) {
    return parent::canShowBlock();
  }
  return false;
}

protected function createCategoriesBlock() {

  $categoryBlock = $this->getLayout()
  ->createBlock('tsearch/catalog_layer_filter_category')
  ->setLayer($this->getLayer())
  ->init();
  $this->setChild('category_filter', $categoryBlock);
}

}
