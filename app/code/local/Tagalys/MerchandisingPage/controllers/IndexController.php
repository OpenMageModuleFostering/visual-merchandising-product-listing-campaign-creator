<?php
/**
 * Merchandising Page Index Controller
 */

class Tagalys_MerchandisingPage_IndexController extends Mage_Core_Controller_Front_Action
{
 public function indexAction ()
 {
    $helper = Mage::helper('merchandisingpage');
    $service = Mage::getSingleton("merchandisingpage/client");
    $status = Mage::helper('tagalys_core')->getTagalysConfig("is_merchandising_page_active");
    if ($status) {
      $response = $helper->getTagalysSearchData();
    }

    $this->loadLayout();
    // var_dump($response);
  
    if(isset($response)) {
      $head = $this->getLayout()->getBlock('head');
      if ($head){
        if(isset($response['variables']['page_title'])) {
          $head->setTitle($response['variables']['page_title']); 
        } else {
          $head->setTitle(ucwords(str_replace('-',' ',$response['product'])).$_SERVER['HOST_NAME'] ); 
        }
        $head->setKeywords($response['variables']['meta_keywords']);
        $head->setDescription($response['variables']['meta_description']);
      }
      $this->renderLayout();
    }
    else {
	return Mage::getClass('Mage_Core_Controller_Varien_Action')->norouteAction();
    }
  }
}
