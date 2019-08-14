<?php
class Tagalys_Tsearch_Block_Page_Html_Pager extends Mage_Page_Block_Html_Pager {

  public function getLastPageNum() {
    $this->_pageSize = $this->getLimit();
    $tagalysData = Mage::helper("tsearch")->getTagalysSearchData();
    if($tagalysData == false) {
      return parent::getLastPageNum();
    } else {
      $collectionSize = (int) $tagalysData["total"];

      if (0 === $collectionSize) {
        return 1;
      }
      elseif($this->_pageSize) {
        return ceil($collectionSize/$this->_pageSize);
      }
      else {
        return 1;
      }
    }
  }

  public function getTotalNum() {
    $tagalysData = Mage::helper("tsearch")->getTagalysSearchData();
    if($tagalysData == false) {
      return parent::getTotalNum();
    } else {
      return (int) $tagalysData["total"];
    }
  }

  public function getLimit() {
    $current_list_mode = Mage::app()->getLayout()->createBlock('catalog/product_list_toolbar')->getCurrentMode();
      
    if( $current_list_mode == "grid" || $current_list_mode == "grid-list") {
      $defaultLimit = Mage::getStoreConfig('catalog/frontend/grid_per_page');
      
    } else if($current_list_mode == "list" || $current_list_mode == "list-grid") {
      $defaultLimit = Mage::getStoreConfig('catalog/frontend/list_per_page');
    }
    
    $session_limit =  $this->getRequest()->getParam($this->getLimitVarName(), $this->getDefaultPerPageValue());

    !empty($session_limit) ? $session_limit : $defaultLimit;
    return !empty($session_limit) ? $session_limit : $defaultLimit;
  }

  public function getPages()
  {
      $collection = $this->getCollection();

      $pages = array();
      if ($this->getLastPageNum() <= $this->_displayPages) {
          $pages = range(1, $this->getLastPageNum());
      }
      else {
          $half = ceil($this->_displayPages / 2);
          if ($collection->getCurPage() >= $half
              && $collection->getCurPage() <= $this->getLastPageNum() - $half
          ) {
              $start  = ($collection->getCurPage() - $half) + 1;
              $finish = ($start + $this->_displayPages) - 1;
          }
          elseif ($collection->getCurPage() < $half) {
              $start  = 1;
              $finish = $this->_displayPages;
          }
          elseif ($collection->getCurPage() > ($this->getLastPageNum() - $half)) {
              $finish = $this->getLastPageNum();
              $start  = $finish - $this->_displayPages + 1;
          }

          $pages = range($start, $finish);
      }
      return $pages;
  }
  
  protected function _initFrame()
{
    if (!$this->isFrameInitialized()) {
        $start = 0;
        $end = 0;

        //$collection = $this->getCollection();
        if ($this->getLastPageNum() <= $this->getFrameLength()) {
            $start = 1;
            $end = $this->getLastPageNum();
        }
        else {
	    $half = ceil($this->getFrameLength() / 2);
	    if ($this->getCurrentPage() >= $half && $this->getCurrentPage() <= $this->getLastPageNum() - $half) {
		$start  = ($this->getCurrentPage() - $half) + 1;
                $end = ($start + $this->getFrameLength()) - 1;
	    }
	    elseif ($this->getCurrentPage() < $half) {
                    $start  = 1;
                    $end = $this->getFrameLength();
	    }
	    elseif ($this->getCurrentPage() > ($this->getLastPageNum() - $half)) {
		    $end = $this->getLastPageNum();
		    $start  = $end - $this->getFrameLength() + 1;
	    }
	    //$start = 1;
	    //$end = $this->getLastPageNum() - abs($this->getLastPageNum() - $this->getFrameLength());

        }
        $this->_frameStart = $start;
        $this->_frameEnd = $end;

        $this->_setFrameInitialized(true);
    }

    return $this;
}


  public function getFramePages()
    {
        $start = $this->getFrameStart();
        $end = $this->getFrameEnd();
        return range($start, $end);
    }

  public function getCurrentPage()
  {
      return (int) $this->getRequest()->getParam($this->getPageVarName(), 1);
  }

  public function getPreviousPageUrl()
  {
        return $this->getPageUrl($this->getCurrentPage() - 1);
  }

  public function getNextPageUrl()
  {
        return $this->getPageUrl($this->getCurrentPage() + 1);
  }
  public function getFirstPageUrl()
  {
        return $this->getPageUrl($this->getLastPageNum());
  }
  public function getLastPageUrl()
  {
        return $this->getPageUrl($this->getLastPageNum());
  }
  public function isFirstPage()
  {
        return $this->getCurrentPage() == 1;
  }
  public function isLastPage()
  {
        return $this->getCurrentPage() == $this->getLastPageNum();
  }
}
