<?php

class Tagalys_Core_Model_Queue extends Mage_Core_Model_Abstract {
    protected function _construct(){
        $this->_init("tagalys_core/queue");
    }

    public function queuePrimaryProductIdFor($product_id) {
        $primary_product_id = $this->getPrimaryProductId($product_id);
        if ($primary_product_id === false) {
            // no related product id
        } elseif ($product_id == $primary_product_id) {
            // same product. so no related product id.
        } else {
            // add primary_product_id and remove product_id
            $this->blindlyAddProduct($primary_product_id);
        }
        return $primary_product_id;
    }

    public function prune($limit = 100) {
        // remove products with visibility 1 (not visible invidivually); replace with the configurable product if exists
        $changed_count = 0;
        $queue_collection = $this->getCollection()->setOrder('id', 'ASC')->setPageSize($limit);
        foreach ($queue_collection as $i => $queue_item) {
            $product_id = $queue_item->getData('product_id');
            $primary_product_id = $this->queuePrimaryProductIdFor($product_id);
            if ($primary_product_id == false || $product_id != $primary_product_id) {
                $queue_item->delete();
            }
        }
        if ($changed_count > 0) {
            prune($limit);
        }
    }

    public function _visibleInAnyStore($product_id) {
        $visible = false;
        $store_ids = Mage::helper("tagalys_core")->getStoresForTagalys();
        foreach ($store_ids as $store_id) {
            Mage::app()->setCurrentStore($store_id);
            $product = Mage::getModel('catalog/product')->load($product_id);
            $product_visibility = $product->getVisibility();
            if ($product_visibility != 1) {
                $visible = true;
                break;
            }
        }
        return $visible;
    }

    public function getPrimaryProductId($product_id) {
        $product = Mage::getModel('catalog/product')->load($product_id);
        if ($product) {
            $product_type = $product->getTypeId();
            $visible_in_any_store = $this->_visibleInAnyStore($product_id);
            if (!$visible_in_any_store) {
                // not visible individually
                if ($product_type == 'simple') {
                    // coulbe be attached to configurable product
                    $parent_ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product_id);
                    if (count($parent_ids) > 0) {
                        // check and return configurable product id
                        return $this->getPrimaryProductId($parent_ids[0]);
                    }
                } else {
                    // configurable / grouped / bundled product that is not visible individually
                    return false;
                }
            } else {
                // any type of product that is visible individually. add to queue.
                return $product_id;
            }
        } else {
            // product not found. might have to delete
            return $product_id;
        }
    }

    public function blindlyAddProduct($product_id) {
        $existingProduct = $this->load($product_id, 'product_id');
        $id_in_queue = $existingProduct->getId();
        if (empty($id_in_queue)) {
            $data = array('product_id' => $product_id);
            $this->setData($data);
            try {
                $this->save();
                return true;
            } catch(Exception $e) {
                Mage::log("Error adding product_id $product_id to tagalys_queue.", null, "tagalys.log");
            }
            return false;
        } else {
            return false;
        }
    }
}
