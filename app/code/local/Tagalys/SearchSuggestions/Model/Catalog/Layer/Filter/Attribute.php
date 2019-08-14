<?php

class Tagalys_SearchSuggestions_Model_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute {

  public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = $request->getParam($this->_requestVar);
        //aadi
        $request = Mage::app()->getRequest()->getParams();
        if (!empty($request["qf"])) {
            foreach (explode("~", $request["qf"]) as $key => $value) {
                $temp = explode("-", $value);
                if($temp[0] == $this->_requestVar) {
                    $temp_val = explode("-", $value);
                    $filter = $temp_val[1];
                }
                # code...
            }
        }
        //aadi end
        if (is_array($filter)) {
            return $this;
        }
        $text = $this->_getOptionText($filter);
        if ($filter && strlen($text)) {
            $this->_getResource()->applyFilterToCollection($this, $filter);
            $this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));
            $this->_items = array();
        }
        return $this;
    }

}