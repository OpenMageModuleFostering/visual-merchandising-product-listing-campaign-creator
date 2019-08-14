<?php
class Tagalys_Sync_Model_Mysql4_Queue extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("sync/queue", "id");
    }
}