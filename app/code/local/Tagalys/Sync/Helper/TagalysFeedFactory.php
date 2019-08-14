<?php
/**
 * Tagalys Feed Wrapper
 *
 * Wrapper class creates tagalys specific product feed in json file.
 */
class Tagalys_Sync_Helper_TagalysFeedFactory extends Varien_Io_File {
  private $default_location; //defualt location of tagalys media directory
  private $file_path; // tagalys directory location
  protected $_api;
  protected $_total;
  
  CONST PAGE_LIMIT = 1000;
  
  function __construct() {
    $this->default_location = Mage::getBaseDir('media'). DS .'tagalys';
    $this->file_path = $this->default_location . DS;
        // parent::__construct();
  }
  public function getProductDump($storeId) {
    if(!Mage::helper('tagalys_core')->getTagalysConfig("sync-".$storeId)){
     return $this->createProductFeed($storeId,true);
   }
   return false;
 }
 public function getUpdatesDump($storeId) {
  return $this->createProductFeed($storeId,false);
}
  /**
   * Create new file
   *
   * @param String timestamp
   */
  public function getTotal($storeId,$dump) {
    if($dump == true){
      $collection = $this->getProductCollection($storeId);
    } else {
      $collection = Mage::getModel('sync/queue')->getCollection()->setOrder('id', 'DESC');
    }
    $total = $collection->count();
    return $total;
  }
  public function createProductFeed($storeId,$dump) {
    $products = array();
    $total = $this->getTotal($storeId,$dump);
    $type =  $dump ? "dump" : "updates";
    $status = $this->isProductFeedProceesed($type);
    $exist_feed = $this->getFeedFile($storeId,$type);
    if(isset($exist_feed)) {
      return false;
    }
    if(!$status) {
      return array( "status" => false,"message"=> "Already some feed creation process in queue. Please try again later.");
    }
    $this->checkAndCreateFolder($this->default_location);
    if(isset($total)) {
      $name = md5(microtime());
      if($dump == true){
        $name = 'dump'.'-'.$name;
        $file = $this->file_path . $name . '-'.$storeId.'-'.$total.'.jsonl';       
      } else {
        $name = 'updates'.'-'.$name;
        $file = $this->file_path . $name . '-'.$storeId.'-'.$total.'.jsonl';    
      }
      $this->setAllowCreateFolders(true);
      $this->open(array('path' => $this->file_path));
      $this->streamOpen($file, 'w+');
      $this->streamClose();
    }
    return array("status" => true,
                 "message" => $name.'.jsonl');
  }
  /**
   * Retuns finished product feed details
   *
   * @return Array finished files
   */
  public function getFinishedProductFeed() {
    $files = $this->getAllProductFeed();
    $finishd_files = array();
    foreach ($files as $key => $value) {
      if (!is_dir($this->file_path . $value) ) {
        if(!preg_match("/^\./", $value)) {
          $file_status = explode( '-', $value );
          if(count($file_status) == 2) {
            array_push($finishd_files, $value);
          } 
        }
      }
    }
    return $finishd_files;
  }
  /**
   * Delete given file 
   *
   * @param String file name
   */
  public function deleteProductFeed($filename) {
    if (!unlink($this->file_path . $filename)) {
      return array( "status" => false,"message"=>"Error while deleting the file ".$filename);
    } else {
      return array("status" => true,"message"=>"The file '".$filename. "' has been deleted successfully.");
    }
  }
  /**
   * Return file meta details from tagalys media directory
   * 
   * @return Object file meta details
   */
  public function getProductFeedMetaDetails($feed= null) {
   $file_meta= new stdClass();
   if(is_null($feed)) {
    $files = $this->getAllProductFeed();
  } else {
    $files = $feed;
    $file_status = explode( '-', $files );
    if(count($file_status) == 2) {
      return true;
    }
  }
  foreach ($files as $key => $value) {
    if (!is_dir($this->file_path . $value) ) {
     if(!preg_match("/^\./", $value)) {
      $file_status = explode( '-', $value );
      if(count($file_status) > 3) {
        $meta_temp = explode( '.', $file_status[3]);
              $total = $meta_temp[0]; //explode('.', $file_status[1]);
              $file_meta->name = $value;
              $file_meta->store = $file_status[2];
              $file_meta->uniq_name =$file_status[1];
              $file_meta->type = $file_status[0];
              $file_meta->total = (int) $total;
              if(preg_match("/^[0-9]+$/",$total)) {
               $file_meta->total = (int) $total;
               $file_meta->status = null;
             } else {
               $file_meta->total = null;
               $file_meta->status = $total;
             }
             return $file_meta;
           } 
         }
       }
     }
     return $file_meta_arr;
   }
  /**
   * Update product feed content
   */
  public function updateProductFeed() {
    try {

      $pages = 0;
      $page_done = 0;
      $products_done = 0;
      $total = 0;
      $page_no = 0;
      $file_meta = $this->getProductFeedMetaDetails();
      if (empty($file_meta)) {
        return false;
      }
      $helper = Mage::helper("sync/service");
      if($file_meta->type == "dump") {
        $total = $this->getTotal($file_meta->store, true);


      } else {
        $total =  Mage::getModel('sync/queue')->getCollection()->count();
      }
      $pages = $total/self::PAGE_LIMIT;
      $feed_file = $this->getFeedFile($file_meta->store, "dump");
      if (!empty($file_meta->type)) {
        $fp = file((String)$this->file_path.$file_meta->type.'-'.$file_meta->uniq_name.'-'.$file_meta->store.'-processing.jsonl', FILE_SKIP_EMPTY_LINES);
        $line_count = count($fp);
        $page_no = $pages - (($total - $line_count)/self::PAGE_LIMIT);
        if (is_float($page_no)) {
          $page_no = round($page_no + 1);
        }
      }

      if($file_meta->status != "processing" && $file_meta->type == "dump" ) {

        rename($this->file_path  . $file_meta->name, $this->file_path . $file_meta->type.'-'.$file_meta->uniq_name.'-'.$file_meta->store.'-processing.jsonl');
        $file = $this->file_path . $file_meta->type.'-'.$file_meta->uniq_name.'-'.$file_meta->store.'-processing.jsonl';

      } else {
        if ($file_meta->type == "dump")
          $file = $this->file_path . $file_meta->type.'-'.$file_meta->uniq_name.'-'.$file_meta->store.'-processing.jsonl';
        if(file_exists($file)){

          if($file_meta->type == "dump") {
           $this->open(array('path' => $this->file_path));
           $this->streamOpen($file, 'a');
           $feeds = Mage::getModel('catalog/product')->getCollection();
           if (isset($pages) && $pages > 0) {
            if (is_float($pages)) {
              $pages = round($pages + 1);
            }
            for ($i = $page_no ; $i <= $pages; $i++) {
              $products_count = $i == 1 ? 1 : ($i-1)*self::PAGE_LIMIT;
              $collection = $this->getProductCollectionByPage($file_meta->store,$i);
              foreach ($collection as $product) {

                $simpleProduct = (array) $helper->getSingleProductWithoutPayload($product->getId());
                $this->streamWrite( json_encode(array("perform" => "index", "payload" => $simpleProduct)) ."\r\n");
                $products_count =  ($products_count) + 1;
                $this->updateStatusFile($file_meta->store, $products_count) ;
              }
              $page_done = $i;

            }
          }
          $this->streamClose();
        } 

      }
      if($page_done == $pages && $file_meta->type == "dump") {

       $fp = file((String)$this->file_path.$file_meta->type.'-'.$file_meta->uniq_name.'-'.$file_meta->store.'-processing.jsonl', FILE_SKIP_EMPTY_LINES);
       $line_count = count($fp);
       $finished_file_name = $file_meta->type.'-'. $file_meta->uniq_name.'-'.$file_meta->store.'.jsonl';
       rename($file, $this->file_path . $file_meta->type.'-'. $file_meta->uniq_name.'-'.$file_meta->store.'.jsonl');

       if($line_count > 0) {
        $this->notify_tagalys($finished_file_name, $line_count, $file_meta->store, $file_meta->type);
      } else {
        $this->deleteProductFeed($finished_file_name);
      }


    }
  }
  if($file_meta->type == "updates" ) {
    $collection = (array) $helper->getProductUpdate($total, $file_meta->store);
    $i = 0;
    $files =  Mage::helper("sync/tagalysFeedFactory")->getAllProductFeed("updates");
    foreach ($files as $key => $value) {
      $products_done = 0;
      $file_meta = Mage::helper("sync/tagalysFeedFactory")->getProductFeedMetaDetails(array($key =>$value));
      if($file_meta->status != "processing" ) {
        rename(Mage::getBaseDir('media'). DS .'tagalys'. DS  . $file_meta->name, Mage::getBaseDir('media'). DS .'tagalys'. DS . $file_meta->type.'-'.$file_meta->uniq_name.'-'.$file_meta->store.'-processing.jsonl');
        $file = Mage::getBaseDir('media'). DS .'tagalys'. DS . $file_meta->type.'-'.$file_meta->uniq_name.'-'.$file_meta->store.'-processing.jsonl';
      } else {
        $file = Mage::getBaseDir('media'). DS .'tagalys'. DS . $file_meta->type.'-'.$file_meta->uniq_name.'-'.$file_meta->store.'-processing.jsonl';
      }

      $this->open(array('path' => Mage::getBaseDir('media'). DS .'tagalys'. DS));
      $this->streamOpen($file, 'a');
      foreach ($collection as $product) {

        $this->streamWrite( json_encode($product) ."\r\n");
        $i = $i + 1;
        $products_done = $i;
      }
      $this->streamClose();
      if($products_done == $total) {
        $fp = file((String)$this->file_path.$file_meta->type.'-'.$file_meta->uniq_name.'-'.$file_meta->store.'-processing.jsonl', FILE_SKIP_EMPTY_LINES);
        $line_count = count($fp);
        $finished_file_name = $file_meta->type.'-'. $file_meta->uniq_name.'-'.$file_meta->store.'.jsonl';
        rename($file, $this->file_path . $file_meta->type.'-'. $file_meta->uniq_name.'-'.$file_meta->store.'.jsonl');

        if($line_count > 0) {
          $this->notify_tagalys($finished_file_name, $line_count, $file_meta->store, $file_meta->type);
        } else {
          $this->deleteProductFeed($finished_file_name);
        }
      }
    }

  }
}
catch (Exception $e){
  Mage::log("TagalysControllerException".print_r($e->getMessage()), null, "tagalys-exception.log");
}
}
public function updateStatusFile($storeId, $products_count) {
  $fp = fopen( $this->file_path.'tagalys-sync-progress-'.$storeId.'.json', 'w');
  fwrite($fp, $products_count);
  fclose($fp);

}
public function notify_tagalys($filename, $count, $storeId, $type) {
  $service = Mage::getSingleton("sync/client");
  $params["store_id"] = $storeId;
    $tagalys_feed_response = $service->notify_tagalys(array("link" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."tagalys/".$filename, "updates_count" => $count, "store" => $storeId,  "callback_url" => Mage::getUrl('tagalys/feed/indexStatus/')), $type);//to-do
    $this->setInitSyncDone($storeId);
    Mage::dispatchEvent('tagalys_event'); //to-check to-do blank
    return true;
  }
  public function setInitSyncDone($storeId) {
    $data = array('path' => "sync-".$storeId,'value' => 1);
    $collection = Mage::getModel('tagalys_core/config')->getCollection()->addFieldToFilter('path',"sync-".$storeId)->getFirstItem();
    if($id = $collection->getId()){
      $model = Mage::getModel('tagalys_core/config')->load($id)->addData($data);
      try {
        $model->setId($id)->save();
      } catch (Exception $e){
        Mage::log("TagalysControllerException".print_r($e->getMessage()), null, "tagalys-exception.log");
      }
    } else {
      $model = Mage::getModel("tagalys_core/config")->setData($data);
      try {
        $insertId = $model->save()->getId();
      } catch (Exception $e){
        Mage::log("TagalysControllerException".print_r($e->getMessage()), null, "tagalys-exception.log");
      }
    }
  }
  /**
   * Return product collection by given timestamp
   *
   * @param String timestamp
   * @return Object production collection
   */
  public function getProductCollectionByPage($storeId, $page) {
    // $_storeId = Mage::app()
    // ->getWebsite(true)
    // ->getDefaultGroup()
    // ->getDefaultStoreId(); 
   $collection = Mage::getResourceModel('catalog/product_collection')
                        ->addAttributeToFilter('status',1) //only enabled product 
                        ->addAttributeToFilter('visibility',array("neq"=>1)) //except not visible individually
                        ->setStoreId($storeId)
                        ->addStoreFilter($storeId)
                        ->setPageSize(self::PAGE_LIMIT)
                        ->addAttributeToSelect('*')
                        ->setCurPage($page);
                        return $collection;
                      }
  /**
   * Return product collection by given timestamp
   *
   * @param String timestamp
   * @return Object production collection
   */
  public function getProductCollection($storeId) {
    // $_storeId = Mage::app()
    // ->getWebsite(true)
    // ->getDefaultGroup()
    // ->getDefaultStoreId();  
    $collection = Mage::getResourceModel('catalog/product_collection')
                      ->addAttributeToFilter('status',1) //only enabled product 
                      ->addAttributeToFilter('visibility',array("neq"=>1)) //except not visible individually 
                      ->setStoreId($storeId)
                      ->addStoreFilter($storeId)

                      ->addAttributeToSelect('*');
                      return $collection;
                    }
  /**
   * Return all file and directory from tagalys media directory
   *
   * @return Array files and directory
   */
  public function getAllProductFeed($type = null) {
    $feeds = scandir($this->default_location);
    $feed_files = array();
    if ($type == null) {
      foreach ($feeds as $key => $value) {
        if (!is_dir($this->file_path . $value) ) {
          if(!preg_match("/^\./", $value)) {
            $file_status = explode( '-', $value );
            if($file_status[0] == "dump" || $file_status[0] == "updates")
              $feed_files[] = $value;
          }
        }
      }
    } else {
      foreach ($feeds as $key => $value) {
        if (!is_dir($this->file_path . $value) ) {
          if(!preg_match("/^\./", $value)) {
            $file_status = explode( '-', $value );
            if($file_status[0] == $type ) {
              $feed_files[] = $value;
            }
          }
        }
      }
    }
    
    
    return $feed_files;
  }
  /**
   * Helper method to check is there any feed to process.
   * @return Bool status of the feed queue
   */
  public function isProductFeedProceesed($type) {
    $feed_count = 0;
    $files = $this->getAllProductFeed($type);
    foreach ($files as $key => $value) {
      if (!is_dir($this->file_path . $value) ) {
        if(!preg_match("/^\./", $value)) {
          $file_status = explode( '-', $value );
          if(count($file_status) > 3) {
            $feed_count++;
          } 
        }
      }
    }
    $feed_type_count = count(Mage::helper("sync/data")->getSelectedStore());
    if($feed_count == 0) {
      return true;
    } elseif ($feed_type_count != $feed_count) {
      return true;
    } else {
      return false;
    }
  }
  public function getFeedFile($storeId, $type){
    $files = $this->getAllProductFeed($type);
    foreach ($files as $key => $value) {
      if (!is_dir($this->file_path . $value) ) {
        $file_status = explode( '-', $value );
        if($file_status[0] == $type && (int)$file_status[2] == $storeId) {
          return $value;
        }
      }
    }
  }
  public function getFeedProgress($storeId, $feed) {
    $line_count = file_get_contents(Mage::getBaseDir("media"). DS ."tagalys" . DS."tagalys-sync-progress-".$storeId.".json");
    fclose($Path);
    $total = $this->getTotal($storeId, true);
    if($line_count == $total || $storeId == null) {
      $percent = 100;
    } else {
      $percent = ($line_count / $total) * 100;
    }
    return number_format( min((int)$percent,100), 0 );
  }
}
