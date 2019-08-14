<?php
/**
 * Overrides default layer view process to define custom filter blocks.
 *
 * @package Tagalys_MerchandisingPage
 * @subpackage Tagalys_MerchandisingPage_Block
 * @author Tagalys
 */
class Tagalys_MerchandisingPage_Block_Catalog_Layer_View extends Mage_Catalog_Block_Layer_View
{
    /**
     * Boolean block name.
     *
     * @var string
     */
    protected $_booleanFilterBlockName;
    /**
     * Registers current layer in registry.
     *
     * @see Mage_Catalog_Block_Product_List::getLayer()
     */
    protected function _construct()
    {
        // die(var_dump('expression'));
        parent::_construct();
       Mage::unregister('current_layer');
       Mage::register('current_layer', $this->getLayer());
   }
    /**
     * Modifies default block names to specific ones if engine is active.
     */
    protected function _initBlocks()
    {
        parent::_initBlocks();
        if (Mage::helper('merchandisingpage')->isTagalysActive()) {
            $this->_categoryBlockName        = 'merchandisingpage/catalog_layer_filter_category';
            $this->_attributeFilterBlockName = 'merchandisingpage/catalog_layer_filter_attribute';
            $this->_priceFilterBlockName     = 'merchandisingpage/catalog_layer_filter_price';
            // $this->_booleanFilterBlockName   = 'merchandisingpage/catalog_layer_filter_boolean';
        }
    }
    /**
     * Prepares layout if engine is active.
     * Difference between parent method is addFacetCondition() call on each created block.
     *
     * @return Tagalys_MerchandisingPage_Block_Catalog_Layer_View
     */
    protected function _prepareLayout()
    {

        if (Mage::helper('merchandisingpage')->isTagalysActive()) {
            $stateBlock = $this->getLayout()->createBlock($this->_stateBlockName)
            ->setLayer($this->getLayer());
            $categoryBlock = $this->getLayout()->createBlock($this->_categoryBlockName)
            ->setLayer($this->getLayer())
            ->init();
            $this->setChild('layer_state', $stateBlock);
            $this->setChild('category_filter', $categoryBlock->addFacetCondition());
            $filterableAttributes = $this->_getFilterableAttributes();
            $filters = array();
            foreach ($filterableAttributes as $attribute) {
                
                if ($attribute->getAttributeCode() == 'price') {
                    $filterBlockName = $this->_priceFilterBlockName;

                } else {
                    $filterBlockName = $this->_attributeFilterBlockName;
                }
     
                if(isset($filterBlockName)) {
                     $filters[$attribute->getAttributeCode() . '_filter'] = $this->getLayout()->createBlock($filterBlockName)
                ->setLayer($this->getLayer())
                ->setAttributeModel($attribute)
                ->init();
                }
               
            }
            foreach ($filters as $filterName => $block) {
                // var_dump($filterName);
                $this->setChild($filterName, $block->addFacetCondition());
            }
            $this->getLayer()->apply();
            $this->getLayer()->getProductCollection()->load();
        } else {
           parent::_prepareLayout();
       }
       return $this;
   }
   public function getRequest(){
    $controller = Mage::app()->getFrontController();
    if ($controller) {
        $this->_request = $controller->getRequest();
    } else {
        throw new Exception(Mage::helper('core')->__("Can't retrieve request object"));
    }
    return $this->_request;
}
    /**
     * Returns current catalog layer.
     *
     * @return Tagalys_MerchandisingPage_Model_Catalog_Layer|Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
 
       return parent::getLayer();
   }
    public function getFilters()
    {
        try {
             $filters = array();
        if ($categoryFilter = $this->_getCategoryFilter()) {
            $filters[] = $categoryFilter;
        }
        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
            $filters[] = $this->getChild($attribute->getAttributeCode() . '_filter');
            
        }
        return $filters;
    } catch (Exception $e) {
        // die("die");
        var_dump($e);
    }
       
    }
   protected function _getFilterableAttributes(){    
    if (Mage::helper('merchandisingpage')->isTagalysActive()) {
        $attributeModel =  Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
        return $attributeModel;
    }
    return parent::_getFilterableAttributes();
}

}
