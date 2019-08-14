<?php
class Tagalys_Search_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        if (Mage::helper('tagalys_core')->isTagalysModuleEnabled('search')) {
            $block = $this->getLayout()->createBlock(
                'Mage_Core_Block_Template',
                'search',
                array('template' => 'tagalys_search/index.phtml')
            );

            $this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
            $this->getLayout()->getBlock('head')->setTitle('Search Results for "' . Mage::app()->getRequest()->getParam('q', null) . '"');
            $this->getLayout()->getBlock('content')->append($block);
            $this->_initLayoutMessages('core/session');
            $this->renderLayout();
        } else {
            $this->norouteAction();
            return;
        }
    }

}