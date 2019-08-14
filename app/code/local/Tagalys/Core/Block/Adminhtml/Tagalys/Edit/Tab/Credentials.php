<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Credentials extends Mage_Adminhtml_Block_Widget_Form
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

		$fieldset = $form->addFieldset('tagalys_core_fieldset', array('legend' => $this->__('Credentials')));

		$fieldset->addField('api_server', 'text', array(
			'name'      => 'api_server',
			'label'     => $this->__('Enter API Server'),
			'value'  => $this->_helper->getTagalysConfig("client_code"),
			'required'  => true,
			'style'   => "width:100%",
			'value'     => $this->_helper->getTagalysConfig("api_server"),
			'after_element_html' => '<small>Server Name can be found in the API Credentials section of your <a href=http://dashboard.tagalys.com/signup target=_blank> Tagalys Account </a></small>',
			'tabindex' => 1
			));


		$fieldset->addField('client_code', 'text', array(
			'label'     => $this->__('Client Code'),
			'required'  => true,
			'name'      => 'client_code',
			'value'  => $this->_helper->getTagalysConfig("client_code"),
			'disabled' => false,
      // 'readonly' => false,
			'style'   => "width:100%",
			'after_element_html' => '<small>Client Code can be found in the API Credentials section of your Tagalys Account </small>',
			'tabindex' => 1
			));

		
		$fieldset->addField('public_api_key', 'text', array(
			'label'     => $this->__('Public API Key'),
			'required'  => true,
			'name'      => 'public_api_key',
			'value'  => $this->_helper->getTagalysConfig("public_api_key"),
			'disabled' => false,
      // 'readonly' => false,
			'style'   => "width:100%",
			'after_element_html' => '<small>This API key which will be included in the public JavaScript code </small>',
			'tabindex' => 1
			));
		$fieldset->addField('private_api_key', 'password', array(
			'label'     => $this->__('Private API Key'),
			'required'  => true,
			'name'      => 'private_api_key',
			'value'  => $this->_helper->getTagalysConfig("private_api_key"),
			'disabled' => false,
      // 'readonly' => false,
			'style'   => "width:100%",
			'after_element_html' => '<small>This API Key is used to authenticate all communications between Client & Tagalys servers(To be kept Private)</small>',
			'tabindex' => 1
			));

		$fieldset->addField('submit', 'submit', array(
			'name' => 'submit_auth',
			'value' => 'Submit',
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
    	return $this->__('Tagalys Credentials');
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle() {
    	return $this->__('Tagalys Credentials');
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