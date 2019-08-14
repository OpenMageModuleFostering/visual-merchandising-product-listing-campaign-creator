<?php
class Tagalys_MerchandisingPage_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar {
    
    public function getAvailableOrders()
  {
    $tagalysData = Mage::helper("merchandisingpage")->getTagalysSearchData();
    if($tagalysData == false) {
      return $this->_availableOrder;
    } else {
      $sort_options =  array();
      foreach ($tagalysData['sort_options'] as $key => $sort) {
        foreach ($sort as $key => $value) {
          foreach ($value as $field => $val) {
            $sort_options[$val["id"]] = $val['label'];
          }
        }
      }
      $sort_options =  array();
      foreach ($tagalysData['sort_options'] as $key => $sort) {
       $sort_options[$sort["id"]]  =$sort["label"];
     }
     $this->_availableOrder = $sort_options;
     return $this->_availableOrder;
   }
 }
 public function getLastPageNum() {
 
  $this->_pageSize = $this->getLimit();
  $tagalysData = Mage::helper("merchandisingpage")->getTagalysSearchData();
  if($tagalysData == false) {
    return parent::getLastPageNum();
  } else {

   $collectionSize = (int) $tagalysData["total"];

   if (0 === $collectionSize) {
    return 1;
  }
  elseif($this->_pageSize) {
    return ceil($collectionSize/$this->_pageSize);
  }
  else{
    return 1;
  }
}
}

public function getTotalNum() {
  $tagalysData = Mage::helper("merchandisingpage")->getTagalysSearchData();
  if($tagalysData == false) {
    return parent::getTotalNum();
  } else {
    return (int) $tagalysData["total"];
  }
}

public function getLimit() {
  $current_list_mode = Mage::app()->getLayout()->createBlock('catalog/product_list_toolbar')->getCurrentMode();
      
      if( $current_list_mode == "grid" || $current_list_mode == "grid-list") {
        $defaultLimit = Mage::getStoreConfig('catalog/frontend/grid_per_page');
        
      } else if($current_list_mode == "list" || $current_list_mode == "list-grid") {
        $defaultLimit = Mage::getStoreConfig('catalog/frontend/list_per_page');
      }
       $session_limit =  $this->getRequest()->getParam($this->getLimitVarName(), $this->getDefaultPerPageValue());

      !empty($session_limit) ? $session_limit : $defaultLimit;
      return !empty($session_limit) ? $session_limit : $defaultLimit;
}

public function getFirstNum()
{
  $tagalysData = Mage::helper("merchandisingpage")->getTagalysSearchData();
  if($tagalysData == false) {
    return parent::getFirstNum();
  } else {
    $this->_pageSize = $this->getLimit();
    return $this->_pageSize*($this->getCurrentPage()-1)+1;
  }
}
public function getLastNum()
{
  $tagalysData = Mage::helper("merchandisingpage")->getTagalysSearchData();
  if($tagalysData == false) {
    return parent::getLastNum();
  } else {
    $this->_pageSize = $this->getLimit();
    $blind_last_num = $this->getFirstNum() + $this->_pageSize - 1;
    $actual_last_num = min($blind_last_num, $tagalysData["total"]);
    return $actual_last_num;
  }
}

}
