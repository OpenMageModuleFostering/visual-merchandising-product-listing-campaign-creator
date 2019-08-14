<?php
/**
 * Overrides default layer view process to define custom filter blocks.
 *
 * @package Tagalys_Tsearch
 * @subpackage Tagalys_Tsearch_Block
 * @author Tagalys
 */
class Tagalys_Tsearch_Block_Catalog_Layer_View extends Mage_Catalog_Block_Layer_View
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
        if (Mage::helper('tsearch')->isTagalysActive()) {
            $this->_categoryBlockName        = 'tsearch/catalog_layer_filter_category';
            $this->_attributeFilterBlockName = 'tsearch/catalog_layer_filter_attribute';
            $this->_priceFilterBlockName     = 'tsearch/catalog_layer_filter_price';
            // $this->_booleanFilterBlockName   = 'tsearch/catalog_layer_filter_boolean';
        }
    }

    /**
     * Prepares layout if engine is active.
     * Difference between parent method is addFacetCondition() call on each created block.
     *
     * @return Tagalys_Tsearch_Block_Catalog_Layer_View
     */
    protected function _prepareLayout()
    {
        if (Mage::helper('tsearch')->isTagalysActive()) {
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
                } elseif ($attribute->getBackendType() == 'decimal') {
                    $filterBlockName = $this->_decimalFilterBlockName;
                } elseif ($attribute->getSourceModel() == 'eav/entity_attribute_source_boolean') {
                    $filterBlockName = $this->_booleanFilterBlockName;
                } else {
                    $filterBlockName = $this->_attributeFilterBlockName;
                }

                $filters[$attribute->getAttributeCode() . '_filter'] = $this->getLayout()->createBlock($filterBlockName)
                ->setLayer($this->getLayer())
                ->setAttributeModel($attribute)
                ->init();
            }

            foreach ($filters as $filterName => $block) {
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
     * @return Tagalys_Tsearch_Model_Catalog_Layer|Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        if (Mage::helper('tsearch')->isTagalysActive()) {
           return Mage::getSingleton('tsearch/catalogsearch');
       }
       return parent::getLayer();
   }
}
