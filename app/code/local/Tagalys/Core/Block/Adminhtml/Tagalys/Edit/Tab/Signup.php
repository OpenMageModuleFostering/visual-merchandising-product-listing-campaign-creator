<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Signup extends Mage_Adminhtml_Block_Widget_Form
implements Mage_Adminhtml_Block_Widget_Tab_Interface {

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

		$form->setHtmlIdPrefix('admin_tagalys_core');
		$htmlIdPrefix = $form->getHtmlIdPrefix();

		$welcome_fieldset = $form->addFieldset('welcome_fieldset', array('legend' => $this->__('General')));

		$welcome_fieldset->addField('note', 'note', array(
			'text'     => '<img src='. $this->getSkinUrl("images/logo-tagalys.png") .' alt="" />'.'<br>'.$this->__('Thank you for downloading Tagalys.'),
			));

		$welcome_fieldset->addField('note_live_server', 'note', array(
			'text'     => $this->__('Please Login/Sign up into your <a href=http://dashboard.tagalys.com/signup target=_blank> Tagalys account </a>.'),
			));

         $welcome_fieldset->addField('submit', 'submit', array(
         'name' => 'submit_signup_next',
         'value' => 'Got my credentials, Proceed !',
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
    	return $this->__('Tagalys Setup');
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle() {
    	return $this->__('Tagalys Setup');
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