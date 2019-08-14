<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Similarproducts extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function __construct() {
        parent::__construct();
    }

    protected function _prepareForm() {
        $this->_helper = Mage::helper('tagalys_core');

        $form = Mage::getModel('varien/data_form', array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/tagalys', array('_current'  => true)),
            'method'  => 'post'
        ));

        $form->setHtmlIdPrefix('admin_tagalys_core_');
        $htmlIdPrefix = $form->getHtmlIdPrefix();

        $fieldset = $form->addFieldset('tagalys_similarproducts_fieldset', array('legend' => $this->__('Similar Products')));

        $fieldset->addField('enable_similarproducts', 'select', array(
            'name' => 'enable_similarproducts',
            'label' => 'Enable',
            'title' => 'Enable',
            'options' => array(
                '0' => $this->__('No'),
                '1' => $this->__('Yes'),
            ),
            'required' => true,
            'disabled' => false,
            'style' => 'width:100%',
            'value' => Mage::getModel('tagalys_core/config')->getTagalysConfig("module:similar_products:enabled")
        ));

        $fieldset->addField('note_integration', 'note', array(
            'label' => 'Integration',
            'text' => 'Paste the following code at a desired location in your product view template at: <code><b>app/design/frontend/<em class="error">PACKAGE-NAME</em>/<em class="error">THEME-NAME</em>/template/catalog/product/view.phtml</b></code><br><br><code>'.htmlentities('<div id="tagalys-namespace" class="tagalys-namespace" data-tagalys-widget="similar_products" data-tagalys-widget-opts-product-id="<?php echo $_product->getId() ?>"></div>').'</code><br><br>',
        ));

        $fieldset->addField('submit', 'submit', array(
            'name' => 'tagalys_submit_action',
            'value' => 'Save Similar Products Settings',
            'class'=> "tagalys-btn",
            'tabindex' => 1
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Tab label getter
     *
     * @return string
     */
    public function getTabLabel() {
        return $this->__('Search Suggestions');
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->__('Search Suggestions');
    }

    /**
     * Check if tab can be shown
     *
     * @return bool
     */
    public function canShowTab() {
        return true;
    }

    /**
     * Check if tab hidden
     *
     * @return bool
     */
    public function isHidden() {
        return false;
    }

}