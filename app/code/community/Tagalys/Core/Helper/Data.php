<?php

class Tagalys_Core_Helper_Data extends Mage_Core_Helper_Abstract {

    public function isTagalysModuleEnabled($module) {
        $store_id = Mage::app()->getStore()->getId();
        $setup_status = Mage::getModel('tagalys_core/config')->getTagalysConfig('setup_status');
        if ($setup_status == 'completed') {
            $store_setup_completed = Mage::getModel('tagalys_core/config')->getTagalysConfig("store:$store_id:setup_complete");
            if ($store_setup_completed == '1') {
                $module_enabled = Mage::getModel('tagalys_core/config')->getTagalysConfig("module:$module:enabled");
                if ($module_enabled == '1') {
                    return true;
                }
            }
        }
        return false;
    }

    public function getProductCategories($id) {
        $clist = "";
        $temp = array();
        $productObj = Mage::getModel('catalog/product')->load($id);
        $categoryIds =  $productObj->getCategoryIds();
        $catId = array();
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
            if (!empty($tempval[1])) {
                $path = $tempval[1]."/".$value;
                $category = explode("/", $path);
                foreach ($category as $category_id) {
                    $categoryList = array();
                    if ($category_id != 1 && $category_id != 2) {
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
    public function getStoresForTagalys() {
        $config_stores = Mage::getModel('tagalys_core/config')->getTagalysConfig("stores");
        
        if ($config_stores) {
            $stores_for_tagalys = json_decode($config_stores, true);
            return $stores_for_tagalys;
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