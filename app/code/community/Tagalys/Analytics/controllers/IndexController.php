<?php
class Tagalys_Analytics_IndexController extends Mage_Core_Controller_Front_Action {

    public function detailsAction() {
        $data = json_decode(Mage::app()->getRequest()->getParam('event_json'), true);
        $productsData = array();
        if ($data[1] == 'product_action') {
            if ($data[2] == 'add_to_cart' || $data[2] == 'buy') {
                for($i = 0; $i < count($data[3]); $i++) {
                    $productsData[] = $this->getProductDetails($data[3][$i]);
                }
            }
        }
        echo json_encode($productsData);
    }

    public function getProductDetails($details) {
        $mainProduct = Mage::getModel('catalog/product')->load($details[1]);
        $productDetails = array(
            'sku' => $mainProduct->getSku(),
            'quantity' => $details[0]
        );
        $noOfItems = count($details);
        if ($noOfItems == 3) {
            $configurableAttributes = array();
            foreach ($mainProduct->getTypeInstance(true)->getConfigurableAttributes($mainProduct) as $attribute) {
                $configurableAttributes[] = $attribute->getProductAttribute()->getAttributeCode();
            }
            $simpleProduct = Mage::getModel('catalog/product')->load($details[2]);
            $product_data = new stdClass();
            $simpleProductAttributes = Mage::getModel("tagalys_core/productDetails")->getProductAttributes($simpleProduct->getId(), Mage::app()->getStore()->getId(), array_keys((array) $product_data));
            $configurableSimpleProductAttributes = array();
            for ($i = 0; $i < count($simpleProductAttributes); $i++) {
                if (in_array($simpleProductAttributes[$i]['tag_set']['id'], $configurableAttributes)) {
                    $configurableSimpleProductAttributes[] = $simpleProductAttributes[$i];
                }
            }
            if (count($configurableSimpleProductAttributes) > 0) {
                $productDetails['__tags'] = $configurableSimpleProductAttributes;
            }
        }
        return $productDetails;
    }

}