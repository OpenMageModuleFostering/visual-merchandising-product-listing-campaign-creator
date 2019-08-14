<?php
/**
 * Merchanding Page Filter Helper 
 */

class Tagalys_MerchandisingPage_Block_Filter extends Mage_Core_Block_Template
{
	 protected function _construct()
    {
        // die('asdfus');
        // $this->setTemplate('catalog/layer/filter.phtml');
    }

    protected function _toHtml() {
        return parent::_toHtml();
    }

    public function canShowBlock() {
      return true;
    }
}