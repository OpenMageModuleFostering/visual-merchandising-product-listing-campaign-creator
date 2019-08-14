<?php
class Tagalys_Core_Model_Mysql4_Queue_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        $this->_init("tagalys_core/queue");
    }
}