<?php
class Tagalys_Analytics_Model_Observer  extends Varien_Object {
    public function addToCart(Varien_Event_Observer $observer) {
        try {
            $mainProduct = $observer->getProduct();
            $productType = $mainProduct->getTypeId();
            $analyticsCookieData = array(1 /* cookie format version */, 'product_action', 'add_to_cart', array(array(Mage::app()->getRequest()->getParam('qty', 1), $mainProduct->getId())));
            if ($productType == 'configurable') {
                $simpleProduct = $observer->getEvent()->getQuoteItem()->getProduct();
                $analyticsCookieData[3][0][] = $simpleProduct->getId();
            }
            Mage::getModel('core/cookie')->set('__ta_event', json_encode($analyticsCookieData));
        } catch (Exception $e) {
            
        }
    }

    public function orderSuccess(Varien_Event_Observer $observer) {
        try {
            $orderIds = $observer->getEvent()->getOrderIds();
            if (empty($orderIds) || !is_array($orderIds)) {
                return;
            }
            $orderId = $orderIds[0];
            $order = Mage::getModel('sales/order')->load($orderId);
            $analyticsCookieData = array(1 /* cookie format version */, 'product_action', 'buy', array(), $orderId);
            
            $returnItems = array();
            foreach($order->getAllItems() as $item){
                $qty = $item->getQtyToShip();
                $product = $item->getProduct();
                if ($product->getTypeId() == 'simple') {
                    $parentId = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
                    if(isset($parentId[0])) {
                        $configurableProduct = Mage::getModel('catalog/product')->load($parentId[0]);
                        $analyticsCookieData[3][] = array((int)$item->getQtyOrdered(), $configurableProduct->getId(), $product->getId());
                    } else {
                        $analyticsCookieData[3][] = array((int)$item->getQtyOrdered(), $product->getId());
                    }
                }
            }
            Mage::register('tagalys_analytics_event', json_encode($analyticsCookieData));
        } catch (Exception $e) {
            
        }
    }
}