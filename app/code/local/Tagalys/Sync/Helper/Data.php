<?php
class Tagalys_Sync_Helper_Data extends Mage_Core_Helper_Abstract
{
  public function getTreeCategories($id){
    $clist = "";
    $productObj = Mage::getModel('catalog/product')->load($id);
    $categoryIds =  $productObj->getCategoryIds();
    foreach ($categoryIds as $key => $value) {
      $category = Mage::getModel('catalog/category')->load($value);
      if ($category->getIsActive()) {
        $catId[] = $value;
      }
    }
    foreach ($catId as $key => $value) {
      $m = Mage::getModel('catalog/category')
      ->load($value)
      ->getParentCategory();
      $path = $m->getPath()."/".$value;
      $category = explode("/", $path);
      foreach ($category as $category_id) {
        if($category_id != 1 && $category_id != 2 ) {
          $_cat = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($category_id);
          $categoryList[] = $_cat->getName();    
        }
      }
      $clist = implode(" / ", $categoryList);
    }
    return $clist;
  }
  public function getProductTreeCat($id) {
    $clist = "";
    $temp = array();
    $productObj = Mage::getModel('catalog/product')->load($id);
    $categoryIds =  $productObj->getCategoryIds();
    foreach ($categoryIds as $key => $value) {
      $category = Mage::getModel('catalog/category')->load($value);
      if ($category->getIsActive()) {
        $catId[] = $value;
      }
    }
    foreach ($catId as $key => $value) {
      $m = Mage::getModel('catalog/category')
      ->load($value)
      ->getParentCategory();
      
      $tempval = explode("1/2",$m->getPath());
      if(!empty($tempval[1])) {
        $path = $tempval[1]."/".$value;
        $category = explode("/", $path);
        foreach ($category as $category_id) {
          $categoryList = array();
          if($category_id != 1 && $category_id != 2 ) {
            $_cat = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($category_id);
            $sub_categories = array("id" => $_cat->getId() , "label" => $_cat->getName());
            $categoryList[] = array("id" => $m->getId() , "label" => $m->getName(), "items" => array($sub_categories));
          }
        }
        $temp[] =  $categoryList[0];
      }
    }
    return $temp;
  }
  public function formatDataForAdminSection($data){
    foreach ($data as $key => $value) {
      $formatted_data[] = array('label' => $key , 'value' => $value);
    }
    return $formatted_data;
  }
  public function getSelectedStore() {
    $this->_config = Mage::helper('tagalys_core')->getTagalysConfig("stores_setup");
    
    if($this->_config){
     $selected_stores = explode(",", $this->_config);
     return $selected_stores;
   }
   return array();
 }
 public function getAllWebsiteStores() {
  foreach (Mage::app()->getWebsites() as $website) {
    foreach ($website->getGroups() as $group) {
      $stores = $group->getStores();
      foreach ($stores as $store) {
        $website_stores[] = array("value" => $store->getId(), "label" => $website->getName()." / ".$group->getName(). " / ".$store->getName());
      }
    }
  }

  return $website_stores;
}
}
