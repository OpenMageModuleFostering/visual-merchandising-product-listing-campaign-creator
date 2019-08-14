<?php
/**
 * Merchandising Page Index Controller
 */

class Tagalys_MerchandisingPage_IndexController extends Mage_Core_Controller_Front_Action
{
 public function indexAction ()
 {
	
    $service = Mage::helper('merchandisingpage');
    $res =  $service->getTagalysSearchData();

    $this->loadLayout();

	
    $head = $this->getLayout()->getBlock('head');
    if ($head){
       if(isset($res['variables']['page_title'])) {
          $head->setTitle($res['variables']['page_title']); 
        } else {
         $head->setTitle(ucwords(str_replace('-',' ',$res['product'])).$_SERVER['HOST_NAME'] ); 
       }
       
       // $head->setTitle(ucwords(str_replace('-',' ',$request['product'])).$_SERVER['HOST_NAME'] ); 
        $head->setKeywords($res['variables']['meta_keywords']);
        $head->setDescription($res['variables']['meta_description']);
    }
    $this->renderLayout();
  }
}