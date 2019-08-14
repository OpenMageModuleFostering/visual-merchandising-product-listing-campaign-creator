<?php
class Tagalys_SearchSuggestions_AutoSuggestController extends Mage_Core_Controller_Front_Action
{
  public function ssAction() {
    try {
      $output = $this->getRequest()->getParams();
      $qf = $output["qf"];
      $q = $output["q"];
      $temp = array();

      $arr = (array)(json_decode($qf));
      foreach($arr as $k => $v)  {
        if($k == "__categories")
          $k = "cat";
        foreach($v as $k1 => $v1) {

          $temp[$k] = $v1;
        }
      }

      $base = Mage::helper('catalogsearch')->getResultUrl($q);
      $search_url = Mage::getUrl("", array("_absolute" => false , "_query" => $temp));
    
      $filters = explode("?", $search_url);
      $link = $base."&".$filters[1];
      echo  $link;
    }
    catch (Exception $e) {
      return false;
    }

  }
}
