<?php
class Tagalys_SearchSuggestions_Model_Observer {
    
    public function cachePopularSearchesCron() { 
        try {
            $utc_now = new DateTime("now", new DateTimeZone('UTC'));
            $time_now = $utc_now->format(DateTime::ATOM);
            Mage::getModel('tagalys_core/config')->setTagalysConfig("heartbeat:cachePopularSearchesCron", $time_now);
            Mage::helper("search_suggestions")->cachePopularSearches();
        } catch (Exception $e) {
            Mage::log("Error in cachePopularSearchesCron: ". $e->getMessage(), null, "tagalys.log");
        }
        return $this;
    }
}