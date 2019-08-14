<?php

class Tagalys_SearchSuggestions_Model_Catalog_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category {

  public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = (int) $request->getParam($this->getRequestVar());
        //aadi
        $request = Mage::app()->getRequest()->getParams();
        if (!empty($request["qf"])) {
            foreach (explode("~", $request["qf"]) as $key => $value) {
                $temp = explode("-", $value);
                if($temp[0] == $this->_requestVar) {
                    $temp_val =  explode("-", $value);
                    $filter = $temp_val[1];
                }
                # code...
            }
        }
        //end aadi
        if (!$filter) {
            return $this;
        }
        $this->_categoryId = $filter;
        Mage::register('current_category_filter', $this->getCategory(), true);

        $this->_appliedCategory = Mage::getModel('catalog/category')
        ->setStoreId(Mage::app()->getStore()->getId())
        ->load($filter);

        if ($this->_isValidCategory($this->_appliedCategory)) {
            $this->getLayer()->getProductCollection()
            ->addCategoryFilter($this->_appliedCategory);

            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_appliedCategory->getName(), $filter)
                );
        }

        return $this;
    }

}