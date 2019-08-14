<?php 
class Tagalys_Tsearch_Block_Catalog_Layer_State extends Mage_Catalog_Block_Layer_State
{
	
public function getActiveFilters()
    {
        $filters = $this->getLayer()->getFilters();
        if (!is_array($filters)) {
            $filters = array();
        }
         // $filters = array();
        return $filters;
    }

	}