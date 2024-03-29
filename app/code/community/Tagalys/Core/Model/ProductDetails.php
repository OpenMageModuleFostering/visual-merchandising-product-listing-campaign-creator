<?php
class Tagalys_Core_Model_ProductDetails extends Mage_Core_Model_Abstract {
    
    protected $syncfield;
    protected $inventorySyncField;

    public function getProductFields($productId, $storeId, $forceRegenerateThumbnail) {
        try {
            $this->_storeId = $storeId;
            $core_helper = Mage::helper('tagalys_core');
            $product = Mage::getModel('catalog/product')->load($productId);
            if (is_null($this->_storeId)) {
                $this->_storeId = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();      
            }
            Mage::app()->setCurrentStore($this->_storeId);
            $productFields = new stdClass();
            $productFields->__id = $product->getId();
            $productFields->name = $product->getName();
            $productFields->link = $product->getProductUrl();
            $productFields->sku = $product->getData('sku');
            $attributes = $product->getTypeInstance(false)->getEditableAttributes($product);
            foreach ($attributes as $attribute) {
                $is_for_display = ((bool)$attribute->getUsedInProductListing() && (bool)$attribute->getIsUserDefined());
                if ($attribute->getIsFilterable() || $attribute->getIsSearchable() || $is_for_display) {
                    $attr = $product->getResource()->getAttribute($attribute->getAttributeCode());
                    if (!$attr->usesSource()) {
                        $field_val = $attribute->getFrontend()->getValue($product);
                        if (!is_null($field_val)) {
                            $productFields->{$attribute->getAttributeCode()} = $attribute->getFrontend()->getValue($product);
                        }
                    }
                }
            }
            $productType = $product->getTypeId();
            switch($productType) {
                case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                    $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                    foreach($associatedProducts as $associatedProduct) {
                        $price[] = $associatedProduct->getFinalPrice();
                        $mrp[] = $associatedProduct->getPrice();
                    }
                    $productFields->sale_price = min($price);
                    $productFields->price = min($mrp);
                    break;
                case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                    $productFields->sale_price = Mage::getModel('bundle/product_price')->getTotalPrices($product,'min',1);
                    $productFields->price = Mage::getModel('bundle/product_price')->getTotalPrices($product,'min',1);
                    break;
                default:
                    $productFields->sale_price = $product->getFinalPrice();
                    $productFields->price = $product->getPrice();
                    break;
            }
            $utc_now = new DateTime("now", new DateTimeZone('UTC'));
            $time_now =  $utc_now->format(DateTime::ATOM);
            $product_data->synced_at = $time_now;
            $productFields->image_url = $this->getProductImageUrl($product, $storeId, $forceRegenerateThumbnail);
            $fields = array('created_at');
            foreach ($fields as $key => $name) {
                $fieldValue = $product->getResource()->getAttribute($name)->getFrontend()->getValue($product);
                $introduced_at = new DateTime((string)$fieldValue);
                $introduced_at->setTimeZone(new DateTimeZone('UTC'));
                $productFields->introduced_at = $introduced_at->format(DateTime::ATOM);
            }
            $productFields->in_stock = Mage::getModel('catalog/product')->load($product->getId())->isSaleable();

            return $productFields;

        } catch (Exception $e) {

        }
    }
    public function getProductType($productId) {
        $product = Mage::getModel('catalog/product')->load($productId);
        $productType = $product->getTypeId();
        return $productType;
    }
    public function getProductParent($productId) {
        $tagalys_parent_id = array();
        $product = Mage::getModel('catalog/product')->load($productId);
        $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($productId);
        if (!$parentIds) {
            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);  
            $parentProducts = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToFilter('entity_id', array('in' => $parentIds))
            ->load();
            $parents = array();
            foreach ($parentProducts as $key => $pro) {
                array_push($parents, $pro->getId());
            }
            if (isset($parents[0])) {
                $tagalys_parent_id = $parents;
            } 
        } 
        return $tagalys_parent_id;
    }
    public function getProductAttributes($productId, $store_id, $unsyncFields)  {
        $product = Mage::getModel('catalog/product')->load($productId);
        $attribute_options_id = null;
        $attriute_option_value = null;
        $type = $product->getTypeId();
        $attributeObj = array();
        $product->setStoreId($store_id);
        $categories = Mage::helper('tagalys_core')->getProductCategories($productId);
        if (count($categories) > 0) {
            $attributeObj[] = array("tag_set" => array("id" => "__categories", "label" => "Categories" ), "items" => ($categories));
        }

        $attributes = $product->getTypeInstance(false)->getEditableAttributes($product);
        foreach ($attributes as $attribute) {
            $is_for_display = ((bool)$attribute->getUsedInProductListing() && (bool)$attribute->getIsUserDefined());
            if ($attribute->getIsFilterable() || $attribute->getIsSearchable() || $is_for_display) {
                $product_attribute = $product->getResource()->getAttribute($attribute->getAttributeCode());
                if ($product_attribute->usesSource()) {
                    // select, multi-select
                    $field_type = $product_attribute->getFrontendInput();
                    $items = array();
                    if ($field_type == 'multiselect') {
                        $value = $product->getData($attribute->getAttributeCode());
                        $ids = explode(',', $value);
                        foreach ($ids as $id) {
                            $label = $attribute->getSource()->getOptionText($id);
                            if ($id != null && $label != false) {
                                $items[] = array('id' => $id, 'label' => $label);
                            }
                        }
                    } else {
                        $value = $product->getData($attribute->getAttributeCode());
                        $label = $product->getResource()->getAttribute($attribute->getAttributeCode())->getFrontend()->getOption($value);
                        if ($value != null && $label != false) {
                            $items[] = array('id' => $value, 'label' => $label);
                        }
                    }
                    if (count($items) > 0) {
                        $attributeObj[] = array("tag_set" => array("id" => $attribute->getAttributeCode(), "label" => $attribute->getFrontend()->getLabel($product) ),"items" => $items);
                    }
                }
            }
        }
        if ($type === "configurable") {
            $productTypeInstance = $product->getTypeInstance(true);
            $configurable_attributes = array_map(function ($el) {
                return $el['attribute_code'];
            }, $productTypeInstance->getConfigurableAttributesAsArray($product));

            $configurable_product = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
            $simple_products_collection = $configurable_product->getUsedProductCollection()->addAttributeToFilter('status', 1)->addAttributeToSelect('*')->addFilterByRequiredOptions();

            foreach($configurable_attributes as $configurable_attribute) {
                $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product',$configurable_attribute);
                $items = array();
                foreach($simple_products_collection as $simple_product){
                    $items[] = array('id' => $simple_product->getData($configurable_attribute), 'label' => $simple_product->getAttributeText($configurable_attribute));
                }
                if (count($items) > 0) {
                    $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $configurable_attribute);
                    $attributeObj[] = array( "tag_set" => array("id" => $configurable_attribute, "label" => $attributeModel->getStoreLabel($store_id)), "items" => $items);
                }
            }
        }

        return ($attributeObj);
    }
    public function getProductImageUrl($product, $storeId, $forceRegenerateThumbnail) {
        try {
            $productImagePath = $product->getImage();
            $baseProductImagePath = Mage::getBaseDir('media') . DS . "catalog" . DS . "product" . $productImagePath;
            if(file_exists($baseProductImagePath)) {
                $imageDetails = getimagesize($baseProductImagePath);
                $width = $imageDetails[0];
                $height = $imageDetails[1];
                if ($width > 1 && $height > 1) {
                    $resizedProductImagePath = Mage::getBaseDir('media') . DS . 'tagalys' . DS . 'product_thumbnails' . $productImagePath;
                    if ($forceRegenerateThumbnail || !file_exists($resizedProductImagePath)) {
                        if (file_exists($resizedProductImagePath)) {
                            unlink($resizedProductImagePath);
                        }
                        $imageObj = new Varien_Image($baseProductImagePath);
                        $imageObj->constrainOnly(TRUE);
                        $imageObj->keepAspectRatio(TRUE);
                        $imageObj->keepFrame(FALSE);
                        $imageObj->resize(300, 300);
                        $imageObj->save($resizedProductImagePath);
                    }
                    if (file_exists($resizedProductImagePath)) {
                        return str_replace('http:', '', Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'tagalys' . DS . 'product_thumbnails' . $productImagePath);
                    } else {
                        // placeholder
                        $placeholder_image = Mage::getStoreConfig("catalog/placeholder/small_image_placeholder");
                        $media_base_url = Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
                        $media_url = $media_base_url . Mage::getModel('catalog/product_media_config')->getBaseMediaUrlAddition();
                        return str_replace('http:', '', $media_url . DS . 'placeholder' . DS . $placeholder_image);
                    }
                }
            }
        } catch(Exception $e) {
            
        }
    }
}