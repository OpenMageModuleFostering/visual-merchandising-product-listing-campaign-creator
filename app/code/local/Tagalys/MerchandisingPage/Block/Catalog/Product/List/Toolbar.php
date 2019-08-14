<?php
class Tagalys_MerchandisingPage_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar {
    public function getAvailableOrders()
    {
      // die('bsdf');
        // $tagalys = Mage::helper("merchandisingpage")->getTagalysSearchData();
      
      $tagalys = Mage::helper("merchandisingpage")->getTagalysSearchData();

        if($tagalys == false) {

            return $this->_availableOrder;
        
        } else {
            
            $data = $tagalys;
            
            $sort_options =  array();
            
            foreach ($data['sort_options'] as $key => $value) {
                $sort_options[$value['id']] = $value['label'];
            }

            $this->_availableOrder = $sort_options;
     
            return $this->_availableOrder;
        }
    }

   public function getOrderDirection($sort_id) {
    // die('test');
    $sort_direction =  array();

    $tagalys = Mage::helper("merchandisingpage")->getTagalysSearchData();

     $data = $tagalys;
         
    foreach ($data['sort_options'] as $key => $value) {
      if (intval($value['id']) === intval($sort_id)) {
        if(sizeof($value['orders']) === 0) {
          $sort_direction[] = null;
        }
             
        elseif (sizeof($value['orders']) === 1)  {
           $sort_direction[] = $value['orders'][0]["order"];
        }
           
        elseif (sizeof($value['orders']) > 1) {
            foreach ($value['orders'] as $key => $value) {
               $sort_direction[] = $value["order"];
            }
        }      
      }
    }

     return $sort_direction;
  }

  public function setDefaultOrder($field)
  {
    $tagalys = Mage::helper("merchandisingpage")->getTagalysSearchData();

    if($tagalys == false) {
        
        if (isset($this->_availableOrder[$field])) {
            $this->_orderField = $field;
        }

        return $this;    
    } else {

        $data = $tagalys; 

        if (isset($this->_availableOrder[$data['sort']])) {
            $this->_orderField = $field;
         }

        return $this;            
    }   
  }

  public function isOrderCurrent($key)
    {
      // $tagalys = Mage::helper("merchandisingpage")->getTagalysSearchData();
      $tagalys = Mage::helper("merchandisingpage")->getTagalysSearchData();
      $order = $tagalys['sort'];
      if (intval($order) == intval($key)) {
        return true;
      }
      return false;
    }
}
