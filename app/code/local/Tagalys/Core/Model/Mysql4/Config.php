<?php
class Tagalys_Core_Model_Mysql4_Config extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("tagalys_core/config","config_id");
    }
}