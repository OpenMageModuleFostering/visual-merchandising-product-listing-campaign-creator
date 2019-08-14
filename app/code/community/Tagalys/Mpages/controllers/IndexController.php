<?php
class Tagalys_Mpages_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();

        if (Mage::helper('tagalys_core')->isTagalysModuleEnabled('mpages')) {
            try {
                $params = Mage::app()->getRequest()->getParams();
                $response = Mage::getSingleton('tagalys_core/client')->storeApiCall(Mage::app()->getStore()->getId().'', '/v1/mpages/'.$params['mpage'], array('request' => array('variables', 'banners')));
                if ($response !== false) {
                    $head = $this->getLayout()->getBlock('head');
                    if ($head) {
                        if (isset($response['variables'])) {
                            if (isset($response['variables']['page_title']) && $response['variables']['page_title'] != '' ) {
                                $head->setTitle($response['variables']['page_title']);
                            } else {
                                $head->setTitle($response['name']);
                            }
                            if (isset($response['variables']['meta_keywords'])) {
                                $head->setKeywords($response['variables']['meta_keywords']);
                            }
                            if (isset($response['variables']['meta_description'])) {
                                $head->setDescription($response['variables']['meta_description']);
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                
            }

            $block = $this->getLayout()->createBlock(
                'Mage_Core_Block_Template',
                'search',
                array('template' => 'tagalys_mpages/index.phtml')
            );
            $this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
            $this->getLayout()->getBlock('content')->append($block);
            $this->_initLayoutMessages('core/session');
            $this->renderLayout();
        } else {
            $this->norouteAction();
            return;
        }
    }

}