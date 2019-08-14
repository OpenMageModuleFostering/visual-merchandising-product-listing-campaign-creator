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

    public function detailsFromCategoryTree($categoriesTree) {
        $detailsTree = array();
        foreach($categoriesTree as $categoryId => $subCategoriesTree) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $thisCategoryDetails = array("id" => $category->getId() , "label" => $category->getName());
            $subCategoriesCount = count($subCategoriesTree);
            if ($subCategoriesCount > 0) {
                $thisCategoryDetails['items'] = $this->detailsFromCategoryTree($subCategoriesTree);
            }
            array_push($detailsTree, $thisCategoryDetails);
        }
        return $detailsTree;
    }

    public function mergeIntoCategoriesTree($categoriesTree, $pathIds) {
        $pathIdsCount = count($pathIds);
        if (!array_key_exists($pathIds[0], $categoriesTree)) {
            $categoriesTree[$pathIds[0]] = array();
        }
        if ($pathIdsCount > 1) {
            $categoriesTree[$pathIds[0]] = $this->mergeIntoCategoriesTree($categoriesTree[$pathIds[0]], array_slice($pathIds, 1));
        }
        return $categoriesTree;
    }

    public function getProductCategories($id) {
        $product = Mage::getModel('catalog/product')->load($id);
        $categoryIds =  $product->getCategoryIds();
        $activeCategoryPaths = array();
        foreach ($categoryIds as $key => $value) {
            $category = Mage::getModel('catalog/category')->load($value);
            if ($category->getIsActive()) {
                $activeCategoryPaths[] = $category->getPath();
            }
        }
        $activeCategoriesTree = array();
        foreach($activeCategoryPaths as $activeCategoryPath) {
            $pathIds = explode('/', $activeCategoryPath);
            // skip the first two levels which are 'Root Catalog' and the Store's root
            $pathIds = array_splice($pathIds, 2);
            $activeCategoriesTree = $this->mergeIntoCategoriesTree($activeCategoriesTree, $pathIds);
        }
        $activeCategoryDetailsTree = $this->detailsFromCategoryTree($activeCategoriesTree);
        return $activeCategoryDetailsTree;
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