<?php
class Tagalys_Core_Model_Mysql4_Queue extends Mage_Core_Model_Mysql4_Abstract {
    protected function _construct() {
        $this->_init("tagalys_core/queue", "id");
    }
    public function truncateIfEmpty() {
        $count = Mage::getModel('tagalys_core/queue')->getCollection()->getSize();
        if ($count == 0) {
            $this->_getWriteAdapter()->query('TRUNCATE TABLE '.$this->getMainTable());
        }
    }
    public function truncate() {
        $this->_getWriteAdapter()->query('TRUNCATE TABLE '.$this->getMainTable());
    }
}