<?php
class Tagalys_Core_Model_Config extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init("tagalys_core/config");
    }

}