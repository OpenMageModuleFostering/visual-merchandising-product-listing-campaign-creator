<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Progress extends Varien_Data_Form_Element_Abstract {
	protected $_storeId;

	public function __construct($attributes = array())
	{
		$this->_storeId = $attributes['store_id'];
		parent::__construct($attributes);
	}


	public function getElementHtml() {   
	
		$html = '
		<div class="tagalys-progress">
			<div class="tagalys-progress-bar" id="sync_store_'.$this->_storeId.'" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width:100%">
				<div class="status sr-only">Please Wait ...</div> 
			</div>
			<div class="row"><div id="sync_msg_'.$this->_storeId.'"></div></div>
		</div>';

		return $html;
	}

	public function getLabel() {
		return Mage::getModel('core/store')->load($this->_storeId)->getName();
	}


}