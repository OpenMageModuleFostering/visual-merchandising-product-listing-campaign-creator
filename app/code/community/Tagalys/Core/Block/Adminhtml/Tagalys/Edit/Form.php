<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        /** @var $form Varien_Data_Form */
        $form =  new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/tagalys/save/', array('id' => $this->getRequest()->getParam('id'))),
            'method'  => 'post'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
}
