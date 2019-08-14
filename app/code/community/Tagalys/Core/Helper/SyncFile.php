<?php
/*
    statuses:
        pending
        processing
        generated_file
        sent_to_tagalys
        finished
*/

class Tagalys_Core_Helper_SyncFile extends Varien_Io_File {
    private $_sync_files_folder_with_path;
    private $_sync_files_path;
    
    public function __construct() {
        $this->_sync_files_folder_with_path = Mage::getBaseDir('media'). DS .'tagalys';
        $this->checkAndCreateFolder($this->_sync_files_folder_with_path);
        $this->_sync_files_path = $this->_sync_files_folder_with_path . DS;
        $this->per_page = 50;
        $this->cron_instance_max_products = 500;
        // parent::__construct();
    }

    public function triggerFeedForStore($store_id) {
        $feed_status = Mage::getModel('tagalys_core/config')->getTagalysConfig("store:$store_id:feed_status", true);
        if ($feed_status == NULL || in_array($feed_status['status'], array('finished'))) {
            $utc_now = new DateTime("now", new DateTimeZone('UTC'));
            $time_now = $utc_now->format(DateTime::ATOM);
            $products_count = $this->getFeedCount($store_id);
            $feed_status = Mage::getModel('tagalys_core/config')->setTagalysConfig("store:$store_id:feed_status", json_encode(array(
                'status' => 'pending',
                'filename' => $this->_getNewSyncFileName($store_id, 'feed'),
                'products_count' => $products_count,
                'completed_count' => 0,
                'updated_at' => $time_now,
                'triggered_at' => $time_now
            )));
            Mage::getModel('tagalys_core/config')->setTagalysConfig("store:$store_id:resync_required", '0');
        } else {

        }
    }

    public function resyncCron() {
        $utc_now = new DateTime("now", new DateTimeZone('UTC'));
        $time_now = $utc_now->format(DateTime::ATOM);
        Mage::getModel('tagalys_core/config')->setTagalysConfig("heartbeat:resyncCron", $time_now);
        $stores = Mage::getModel('tagalys_core/config')->getTagalysConfig("stores", true);
        if ($stores != NULL) {
            foreach ($stores as $i => $store_id) {
                $resync_required = Mage::getModel('tagalys_core/config')->getTagalysConfig("store:$store_id:resync_required");
                if ($resync_required == '1') {
                    $this->triggerFeedForStore($store_id);
                    Mage::getModel('tagalys_core/config')->setTagalysConfig("store:$store_id:resync_required", '0');
                }
            }
        }
    }

    public function cron() {
        $utc_now = new DateTime("now", new DateTimeZone('UTC'));
        $time_now = $utc_now->format(DateTime::ATOM);
        $cron_heartbeat_sent = Mage::getModel('tagalys_core/config')->getTagalysConfig("cron_heartbeat_sent");
        if ($cron_heartbeat_sent == false) {
            Mage::getSingleton('tagalys_core/client')->log('info', 'Cron heartbeat');
            $cron_heartbeat_sent = Mage::getModel('tagalys_core/config')->setTagalysConfig("cron_heartbeat_sent", true);
        }
        Mage::getModel('tagalys_core/config')->setTagalysConfig("heartbeat:cron", $time_now);
        $stores = Mage::getModel('tagalys_core/config')->getTagalysConfig("stores", true);
        if ($stores != NULL) {
            $this->_checkAndSyncConfig();
            // get product ids from update queue to be processed in this cron instance
            $product_ids_from_updates_queue_for_cron_instance = $this->_productIdsFromUpdatesQueueForCronInstance();
            // products from obervers are added to queue without any checks. so add related configurable products if necessary
            foreach($product_ids_from_updates_queue_for_cron_instance as $product_id) {
                Mage::getModel('tagalys_core/queue')->queuePrimaryProductIdFor($product_id);
            }
            $updates_performed = array();
            foreach($stores as $i => $store_id) {
                $updates_performed[$store_id] = $this->_cronForStore($store_id, $product_ids_from_updates_queue_for_cron_instance);
            }
            $updates_performed_for_all_stores = true;
            foreach ($stores as $i => $store_id) {
                if ($updates_performed[$store_id] == false) {
                    $updates_performed_for_all_stores = false;
                    break;
                }
            }
            if ($updates_performed_for_all_stores) {
                $this->_deleteProductIdsFromUpdatesQueueForCronInstance($product_ids_from_updates_queue_for_cron_instance);
                Mage::getResourceModel('tagalys_core/queue')->truncateIfEmpty();
            }
        }
        return true;
    }

    public function tagalysCallback($store_id, $filename) {
        $type = null;
        if (strpos($filename, '-feed-') !== false) {
            $type = 'feed';
        } elseif (strpos($filename, '-updates-') !== false) {
            $type = 'updates';
        }
        $config_model = Mage::getModel('tagalys_core/config');
        $sync_file_status = $config_model->getTagalysConfig("store:$store_id:{$type}_status", true);
        if ($sync_file_status != null) {
            if ($sync_file_status['status'] == 'sent_to_tagalys') {
                if ($sync_file_status['filename'] == $filename) {
                    if (!unlink($this->_sync_files_path . $filename)) {
                        Mage::getSingleton('tagalys_core/client')->log('warn', 'Unable to delete file in tagalysCallback', array('sync_file_status' => $sync_file_status, 'filename' => $filename));
                    }
                    $sync_file_status['status'] = 'finished';
                    $utc_now = new DateTime("now", new DateTimeZone('UTC'));
                    $time_now = $utc_now->format(DateTime::ATOM);
                    $sync_file_status['updated_at'] = $time_now;
                    $config_model->setTagalysConfig("store:$store_id:{$type}_status", $sync_file_status, true);
                    if ($type == 'feed') {
                        $config_model->setTagalysConfig("store:$store_id:setup_complete", '1');
                        Mage::getSingleton('tagalys_core/client')->log('info', 'Feed sync completed.', array('store_id' => $store_id));
                        $config_model->checkStatusCompleted();
                    } else {
                        Mage::getSingleton('tagalys_core/client')->log('info', 'Updates sync completed.', array('store_id' => $store_id));
                    }
                } else {
                    Mage::getSingleton('tagalys_core/client')->log('warn', 'Unexpected filename in tagalysCallback', array('sync_file_status' => $sync_file_status, 'filename' => $filename));
                }
            } else {
                Mage::getSingleton('tagalys_core/client')->log('warn', 'Unexpected tagalysCallback trigger', array('sync_file_status' => $sync_file_status, 'filename' => $filename));
            }
        } else {
            // TODO handle error
        }
    }

    public function getFeedCount($store_id) {
        $sync_collection = $this->_getCollection($store_id, 'feed');
        $products_count = $sync_collection->count();
        return $products_count;
    }

    public function _checkAndSyncConfig() {
        $config_model = Mage::getModel('tagalys_core/config');
        $config_sync_required = $config_model->getTagalysConfig('config_sync_required');
        if ($config_sync_required == '1') {
            Mage::helper("tagalys_core/service")->syncClientConfiguration();
            $config_model->setTagalysConfig('config_sync_required', '0');
        }
    }

    public function _getDomain() {
        $base_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $base_url = rtrim($base_url, '/');
        $exploded_1 = explode("://", $base_url);
        $replaced_1 = str_replace("-", "__", $exploded_1[1]);
        return str_replace("/", "___", $replaced_1);
    }
    public function _getNewSyncFileName($store_id, $type) {
        $domain =  $this->_getDomain();
        $datetime = date("YmdHis");
        return "syncfile-$domain-$store_id-$type-$datetime.jsonl";
    }
    public function _updateProductsCount($store_id, $type, $collection) {
        $products_count = $collection->count();
        $config_model = Mage::getModel('tagalys_core/config');
        $sync_file_status = $config_model->getTagalysConfig("store:$store_id:{$type}_status", true);
        if ($sync_file_status != NULL) {
            $sync_file_status['products_count'] = $products_count;
            $config_model->setTagalysConfig("store:$store_id:{$type}_status", $sync_file_status, true);
        }
        return $products_count;
    }
    public function _productIdsFromUpdatesQueueForCronInstance() {
        $queue_collection = Mage::getModel('tagalys_core/queue')->getCollection()->setOrder('id', 'ASC')->setPageSize($this->cron_instance_max_products);
        $product_ids_from_updates_queue_for_cron_instance = array();
        foreach ($queue_collection as $i => $queue_item) {
            $product_id = $queue_item->getData('product_id');
            array_push($product_ids_from_updates_queue_for_cron_instance, $product_id);
        }
        return $product_ids_from_updates_queue_for_cron_instance;
    }
    public function _deleteProductIdsFromUpdatesQueueForCronInstance($product_ids_from_updates_queue_for_cron_instance) {
        $collection = Mage::getModel('tagalys_core/queue')
            ->getCollection()
            ->addFieldToFilter('product_id', array( 'in' => $product_ids_from_updates_queue_for_cron_instance));
        foreach($collection as $queue_item) {
            $queue_item->delete();
        }
    }
    public function _getCollection($store_id, $type, $product_ids_from_updates_queue_for_cron_instance = array()) {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId($store_id)
            ->addStoreFilter($store_id)
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', array("neq" => 1))
            ->addAttributeToSelect('entity_id');
        if ($type == 'updates') {
            $collection = $collection->addAttributeToFilter('entity_id', array('in' => $product_ids_from_updates_queue_for_cron_instance));
        }
        return $collection;
    }

    public function _cronForStore($store_id, $product_ids_from_updates_queue_for_cron_instance) {
        $updates_performed = false;
        $feed_response = $this->_generateFilePart($store_id, 'feed');
        $sync_file_status = $feed_response['sync_file_status'];
        if (!$this->_isFeedGenerationInProgress($store_id, $sync_file_status)) {
            if (count($product_ids_from_updates_queue_for_cron_instance) > 0) {
                $updates_response = $this->_generateFilePart($store_id, 'updates', $product_ids_from_updates_queue_for_cron_instance);
                if (isset($updates_response['updates_performed']) and $updates_response['updates_performed']) {
                    $updates_performed = true;
                }
            }
        }
        return $updates_performed;
    }

    public function _isFeedGenerationInProgress($store_id, $store_feed_status) {
        if ($store_feed_status == null) {
            return false;
        }
        if (in_array($store_feed_status['status'], array('finished'))) {
            return false;
        }
        return true;
    }

    public function _checkLock($sync_file_status) {
        if ($sync_file_status['locked_by'] == null) {
            return true;
        } else {
            // some other process has claimed the thread. if a crash occours, check last updated at < 15 minutes ago and try again.
            $locked_at = new DateTime($sync_file_status['updated_at']);
            $now = new DateTime();
            $interval_seconds = $now->getTimestamp() - $locked_at->getTimestamp();
            $min_seconds_for_override = 10 * 60;
            if ($interval_seconds > $min_seconds_for_override) {
                Mage::getSingleton('tagalys_core/client')->log('warn', 'Overriding stale locked process', array('pid' => $sync_file_status['locked_by'], 'locked_seconds_ago' => $interval_seconds));
                return true;
            } else {
                Mage::getSingleton('tagalys_core/client')->log('warn', 'Sync file generation locked by another process', array('pid' => $sync_file_status['locked_by'], 'locked_seconds_ago' => $interval_seconds));
                return false;
            }
        }
    }

    public function _reinitializeUpdatesConfig($store_id, $product_ids_from_updates_queue_for_cron_instance) {
        $utc_now = new DateTime("now", new DateTimeZone('UTC'));
        $time_now = $utc_now->format(DateTime::ATOM);
        $updates_count = count($product_ids_from_updates_queue_for_cron_instance);
        $sync_file_status = array(
            'status' => 'pending',
            'filename' => $this->_getNewSyncFileName($store_id, 'updates'),
            'products_count' => $updates_count,
            'completed_count' => 0,
            'updated_at' => $time_now,
            'triggered_at' => $time_now
        );
        Mage::getModel('tagalys_core/config')->setTagalysConfig("store:$store_id:updates_status", $sync_file_status, true);
        return $sync_file_status;
    }

    public function _generateFilePart($store_id, $type, $product_ids_from_updates_queue_for_cron_instance = array()) {
        $pid = Mage::helper('core')->getRandomString(24);

        Mage::getSingleton('tagalys_core/client')->log('local', '1. Started _generateFilePart', array('pid' => $pid, 'store_id' => $store_id, 'type' => $type));

        $updates_performed = false;
        $config_model = Mage::getModel('tagalys_core/config');
        $sync_file_status = $config_model->getTagalysConfig("store:$store_id:{$type}_status", true);
        if ($sync_file_status == NULL) {
            if ($type == 'feed') {
                // if feed_status config is missing, generate it.
                $this->triggerFeedForStore($store_id);
            }
            if ($type == 'updates') {
                $this->_reinitializeUpdatesConfig($store_id, $product_ids_from_updates_queue_for_cron_instance);
            }
        }
        $sync_file_status = $config_model->getTagalysConfig("store:$store_id:{$type}_status", true);

        Mage::getSingleton('tagalys_core/client')->log('local', '2. Read / Initialized sync_file_status', array('pid' => $pid, 'store_id' => $store_id, 'type' => $type, 'sync_file_status' => $sync_file_status));

        if ($sync_file_status != NULL) {
            if ($type == 'updates' && in_array($sync_file_status['status'], array('finished'))) {
                // if updates are finished, reset config
                $this->_reinitializeUpdatesConfig($store_id, $product_ids_from_updates_queue_for_cron_instance);
                $sync_file_status = $config_model->getTagalysConfig("store:$store_id:{$type}_status", true);
            }

            if (in_array($sync_file_status['status'], array('pending', 'processing'))) {
                if ($this->_checkLock($sync_file_status) == false) {
                    return compact('sync_file_status');
                }

                Mage::getSingleton('tagalys_core/client')->log('local', '3. Unlocked', array('pid' => $pid, 'store_id' => $store_id, 'type' => $type));

                $deleted_ids = array();
                if ($type == 'updates') {
                    $collection = $this->_getCollection($store_id, $type, $product_ids_from_updates_queue_for_cron_instance);
                    $product_ids_in_collection = array();
                    $select = $collection->getSelect();
                    $products = $select->query();
                    foreach($products as $product) {
                        array_push($product_ids_in_collection, $product['entity_id']);
                    }
                    $deleted_ids = array_diff($product_ids_from_updates_queue_for_cron_instance, $product_ids_in_collection);
                } else {
                    $collection = $this->_getCollection($store_id, $type);
                }
                $select = $collection->getSelect();

                // set updated_at as this is used to check for stale processes
                $utc_now = new DateTime("now", new DateTimeZone('UTC'));
                $time_now = $utc_now->format(DateTime::ATOM);
                $sync_file_status['updated_at'] = $time_now;
                // update products count
                $products_count = $this->_updateProductsCount($store_id, $type, $collection);
                if ($products_count == 0 && count($deleted_ids) == 0) {
                    if ($type == 'feed') {
                        Mage::getSingleton('tagalys_core/client')->log('warn', 'No products for feed generation', array('store_id' => $store_id, 'sync_file_status' => $sync_file_status));
                    }
                    $sync_file_status['status'] = 'finished';
                    $config_model->setTagalysConfig("store:$store_id:{$type}_status", $sync_file_status, true);
                    $updates_performed = true;
                    return compact('sync_file_status', 'updates_performed');
                } else {
                    $sync_file_status['locked_by'] = $pid;
                    // set status to processing
                    $sync_file_status['status'] = 'processing';
                    $config_model->setTagalysConfig("store:$store_id:{$type}_status", $sync_file_status, true);
                }

                Mage::getSingleton('tagalys_core/client')->log('local', '4. Locked with pid', array('pid' => $pid, 'store_id' => $store_id, 'type' => $type, 'sync_file_status' => $sync_file_status));

                // setup file
                $this->open(array('path' => $this->_sync_files_path));
                $this->streamOpen($sync_file_status['filename'], 'a');

                foreach($deleted_ids as $i => $deleted_id) {
                    $this->streamWrite(json_encode(array("perform" => "delete", "payload" => array('__id' => $deleted_id))) ."\r\n");
                }

                $cron_instance_completed_products = 0;

                $time_start = time();
                if ($products_count == 0) {
                    $file_generation_completed = true;
                } else {
                    $file_generation_completed = false;

                    if ($type == 'feed') {
                        $total_remaining_products = $sync_file_status['products_count'] - $sync_file_status['completed_count'];
                        $cron_instance_total_products = min($total_remaining_products, $this->cron_instance_max_products);
                    }
                    if ($type == 'updates') {
                        $cron_instance_total_products = $products_count; // already limited to product_ids_from_updates_queue_for_cron_instance
                    }
                    
                    // avoid infinite loops due to undetected bugs / unexpected issues
                    // use a circut breaker with limit of 26 (1000 products per cron instance and 50 products per page = 25. so 26 is not expected.)
                    $circuit_breaker = 0;
                    try {
                        while($cron_instance_completed_products < $cron_instance_total_products && $circuit_breaker < 26) {
                            $circuit_breaker += 1;
                            $products = $select->limit($this->per_page, $sync_file_status['completed_count'])->query();
                            foreach($products as $product) {
                                $forceRegenerateThumbnail = false;
                                if ($type == 'updates') {
                                    $forceRegenerateThumbnail = true;
                                }
                                $product_details = (array) Mage::helper("tagalys_core/service")->getProductPayload($product['entity_id'], $store_id, $forceRegenerateThumbnail);
                                $this->streamWrite(json_encode(array("perform" => "index", "payload" => $product_details)) ."\r\n");
                                $sync_file_status['completed_count'] += 1;
                                $cron_instance_completed_products += 1;
                                $utc_now = new DateTime("now", new DateTimeZone('UTC'));
                                $time_now = $utc_now->format(DateTime::ATOM);
                                $sync_file_status['updated_at'] = $time_now;
                                $config_model->setTagalysConfig("store:$store_id:{$type}_status", $sync_file_status, true);
                            }
                        }
                        $time_end = time();
                    } catch (Exception $e) {
                        Mage::getSingleton('tagalys_core/client')->log('error', 'Exception in generateFilePart', array('store_id' => $store_id, 'sync_file_status' => $sync_file_status));
                    }
                    if ($type == 'feed') {
                        $total_remaining_products = $sync_file_status['products_count'] - $sync_file_status['completed_count'];
                        // $circuit_breaker of 26 is not expected unless we're counting wrong. in that case, complete
                        if ($total_remaining_products <= 0 || $circuit_breaker >= 26) {
                            $file_generation_completed = true;
                            if ($circuit_breaker >= 26) {
                                Mage::getSingleton('tagalys_core/client')->log('error', 'Circuit breaker triggered. Sync file marked as completed.', array('pid' => $pid, 'store_id' => $store_id, 'sync_file_status' => $sync_file_status));
                            }
                        }
                    }
                    if ($type == 'updates') {
                        $file_generation_completed = true; // updates are sent at every cron instance even if queue is larger
                    }
                }
                $updates_performed = true;
                // close file outside of try/catch
                $this->streamClose();
                // remove lock
                $sync_file_status['locked_by'] = null;
                $utc_now = new DateTime("now", new DateTimeZone('UTC'));
                $time_now = $utc_now->format(DateTime::ATOM);
                $sync_file_status['updated_at'] = $time_now;
                $time_end = time();
                $time_elapsed = $time_end - $time_start;
                if ($file_generation_completed) {
                    $sync_file_status['status'] = 'generated_file';
                    $sync_file_status['completed_count'] += count($deleted_ids);
                    Mage::getSingleton('tagalys_core/client')->log('info', 'Completed writing ' . $sync_file_status['completed_count'] . ' products to '. $type .' file. Last batch of ' . $cron_instance_completed_products . ' took ' . $time_elapsed . ' seconds.', array('store_id' => $store_id, 'sync_file_status' => $sync_file_status));
                } else {
                    Mage::getSingleton('tagalys_core/client')->log('info', 'Written ' . $sync_file_status['completed_count'] . ' out of ' . $sync_file_status['products_count'] . ' products to '. $type .' file. Last batch of ' . $cron_instance_completed_products . ' took ' . $time_elapsed . ' seconds', array('store_id' => $store_id, 'sync_file_status' => $sync_file_status));
                    $sync_file_status['status'] = 'pending';
                }
                $config_model->setTagalysConfig("store:$store_id:{$type}_status", $sync_file_status, true);
                Mage::getSingleton('tagalys_core/client')->log('local', '5. Removed lock', array('pid' => $pid, 'store_id' => $store_id, 'type' => $type, 'sync_file_status' => $sync_file_status));
                if ($file_generation_completed) {
                    $this->_sendFileToTagalys($store_id, $type, $sync_file_status);
                }
            } elseif (in_array($sync_file_status['status'], array('generated_file'))) {
                $this->_sendFileToTagalys($store_id, $type, $sync_file_status);
            }
        } else {
            Mage::getSingleton('tagalys_core/client')->log('error', 'Unexpected error in generateFilePart. sync_file_status is NULL', array('store_id' => $store_id));
        }
        return compact('sync_file_status', 'updates_performed');
    }

    public function _sendFileToTagalys($store_id, $type, $sync_file_status = null) {
        if ($sync_file_status == null) {
            $config_model = Mage::getModel('tagalys_core/config');
            $sync_file_status = $config_model->getTagalysConfig("store:$store_id:{$type}_status", true);
        }

        if (in_array($sync_file_status['status'], array('generated_file'))) {
            $api_client = Mage::getSingleton("tagalys_core/client");

            $baseUrl = '';
            $webUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
            if (strpos($mediaUrl, $webUrl) === false) {
                // media url different from website url - probably a CDN. use website url to link to the file we create
                $baseUrl = $webUrl . 'media/';
            } else {
                $baseUrl = $mediaUrl;
            }
            $link_to_file = $baseUrl . "tagalys/" . $sync_file_status['filename'];

            $config_model = Mage::getModel('tagalys_core/config');
            $sync_file_status = $config_model->getTagalysConfig("store:$store_id:{$type}_status", true);
            $data = array(
                'link' => $link_to_file,
                'updates_count' => $sync_file_status['products_count'],
                'store' => $store_id,
                'callback_url' => Mage::getUrl('tagalys/syncfiles/callback/')
            );
            $response = $api_client->storeApiCall($store_id, "/v1/products/sync_$type", $data);
            if ($response != false && $response['result']) {
                $sync_file_status['status'] = 'sent_to_tagalys';
                $config_model->setTagalysConfig("store:$store_id:{$type}_status", $sync_file_status, true);
            } else {
                Mage::getSingleton('tagalys_core/client')->log('error', 'Unexpected response in _sendFileToTagalys', array('store_id' => $store_id, 'sync_file_status' => $sync_file_status, 'response' => $response));
            }
        } else {
            Mage::getSingleton('tagalys_core/client')->log('error', 'Error: Called _sendFileToTagalys with sync_file_status ' . $sync_file_status['status'], array('store_id' => $store_id, 'sync_file_status' => $sync_file_status));
        }
    }
}