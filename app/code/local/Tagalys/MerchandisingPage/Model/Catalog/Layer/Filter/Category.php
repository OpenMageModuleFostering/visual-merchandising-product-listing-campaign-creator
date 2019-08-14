<?php
/**
 * Handles category filtering in layered navigation.
 *
 * @package Tagalys_Tsearch
 * @subpackage Tagalys_Tsearch_Model
 * @author Tagalys
 */
class Tagalys_MerchandisingPage_Model_Catalog_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category
{
    /**
     * Adds category filter to product collection.
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Tagalys_Tsearch_Model_Catalog_Layer_Filter_Category
     */
    public function addCategoryFilter($category)
    {
      $value = array(
        'categories' => $category->getId()
        );
      $this->getLayer()->getProductCollection()
      ->addFqFilter($value);

      return $this;
  }

    /**
     * Adds facet condition to product collection.
     *
     * @see Tagalys_Tsearch_Model_Resource_Catalog_Product_Collection::addFacetCondition()
     * @return Tagalys_Tsearch_Model_Catalog_Layer_Filter_Category
     */
    public function addFacetCondition()
    {
      /** @var $category Mage_Catalog_Model_Category */
      $category = $this->getCategory();
      $childrenCategories = $category->getChildrenCategories();

      $useFlat = (bool) Mage::getStoreConfig('catalog/frontend/flat_catalog_category');
      $categories = ($useFlat)
      ? array_keys($childrenCategories)
      : array_keys($childrenCategories->toArray());

      $this->getLayer()->getProductCollection();

      return $this;
  }



    protected function _getItemsData()
    {
        $key = $this->getLayer()->getStateKey().'_SUBCATEGORIES';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);
        
        if ($data === null) {
            $tagalys = Mage::helper('merchandisingpage')->getTagalysSearchData();
            // $service = Mage::getModel("Tagalys_MerchandisingPage_Model_Client");
            // $tagalys = $service->merchandisingPage(array())['filters'];
            $filters = $tagalys['filters'];

            foreach ($filters as $filter) { 
                if($filter["id"] == "__categories"){
                    $tagalys_categories = $filter;
                }
            }


            $data = array();
            
            foreach ($tagalys_categories["items"] as $category) { 
                if($category["selected"] == true) {
                    foreach ($category["items"] as $sub_category) { 
                     $data[] = array(
                        'label' => $sub_category["name"],
                        'value' => $sub_category["id"],
                        'count' => $sub_category["count"],
                        );
                 }

                 $tags = $this->getLayer()->getStateTags();
                 $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
                 return $data;
            
         } else {
            if (true || $category->getIsActive()) {
                $data[] = array(
                    'label' => $category["name"],
                    'value' => $category["id"],
                    'count' => $category["count"],
                    );
            }
        }
                //check if active

    }
    $tags = $this->getLayer()->getStateTags();
    $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
}
return $data;
}
}
