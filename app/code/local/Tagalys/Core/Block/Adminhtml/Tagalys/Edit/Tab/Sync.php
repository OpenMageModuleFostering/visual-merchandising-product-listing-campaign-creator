<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Sync extends Mage_Adminhtml_Block_Widget_Form
implements Mage_Adminhtml_Block_Widget_Tab_Interface {

	public function __construct() {
		parent::__construct();
	}


	protected function _prepareForm() {
		$sync_helper = Mage::helper("sync/service");
		$this->_helper = Mage::helper('tagalys_core');
		$this->_feed = Mage::helper("sync/tagalysFeedFactory");

		/** @var $form Varien_Data_Form */
		$form = Mage::getModel('varien/data_form', array(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/tagalys', array('_current'  => true)),
			'method'  => 'post'
			));

		$form->setHtmlIdPrefix('admin_');
		$htmlIdPrefix = $form->getHtmlIdPrefix();

   foreach (Mage::helper("sync/data")->getSelectedStore() as $key => $value){
     if(!Mage::helper('tagalys_core')->checkStoreInitSync($value)) {
       $display = true;
       break;
     } else {
      $display = false;
    }
  }
  

  $product_feed = Mage::helper("sync/tagalysFeedFactory")->getAllProductFeed();
  if(!empty($product_feed)) {
  $init_email = $form->addFieldset('tagalys_email_fieldset', array(
      'style'   => "width:100%",
      // 'class' => 'inline'
      ));

    $init_email->addField('email_note', 'note', array(
      'after_element_html' =>'<b>You can close this screen now. We will notify you via e-mail when the sync is completed.</b>' 
      ));

    $fieldset = $form->addFieldset('tagalys_sync_fieldset', array(
     'class' => 'tagalys-progress-inline'
     ));

    $search_fieldset = $form->addFieldset('tagalys_search_fieldset', array(
     'class' => 'tagalys-progress-inline'
     ));

    $selected_stores = Mage::helper("sync/data")->getSelectedStore();

    $fieldset->addField('note_sync', 'note', array(
     'label'     => $this->__('Feed Creation Status 4(a)'),
     'after_element_html' => '<small>Feed creation is processed one store at a time.</small>',
     ));

    $fieldset->addType('tagalys_custom_feed_progress', 'Tagalys_Core_Block_Adminhtml_Tagalys_Progress');     
    foreach ($selected_stores as $key => $value) {
      $fieldset->addField('tagalys_feed_'.$value, 'tagalys_custom_feed_progress', array(
        'store_id' => $value,
        'after_element_html' => '<small>Please wait while your Catalog feed file is being generated.</small>',
        ));
    }

    $search_fieldset->addField('note_search', 'note', array(
     'label'     => $this->__('Tagalys Product Index status 4(b)'),
     ));

    $search_fieldset->addType('tagalys_search_progress', 'Tagalys_Core_Block_Adminhtml_Tagalys_SearchReady');  
    foreach ($selected_stores as $key => $value) {   
     $search_fieldset->addField('tagalys_search_'.$value, 'tagalys_search_progress', array(
      'store_id' => $value,
      'after_element_html' => '<small>Please wait while your products are been processed by Tagalys.</small>'
      ));
   }


 }


 $status = $form->addFieldset('tagalys_status_fieldset', array(
  'style'   => "width:50%",
  'class' => 'inline'
  ));



 $status->addField('cron_note', 'note', array(
  'after_element_html' => Mage::helper('tagalys_core')->getTagalysConfig('setup_complete') ? '<small>Incremental updates will happen every 5 minutes. </small>' : '<small>The duration for Step 4 in setup (Catalog sync) will depend on your cron frequency/setting, catalog size and server throughput. Initial catalog sync usually process around 1000 products per cron.<br><b>We will notify you via e-mail when the sync is completed.</b></small>' ,
  ));

 $status->addField('tagalys_reload', 'submit', array(
  'name' => 'submit_reload',
  'value' => 'Next to Continue',
  'class'=> "tagalys-btn",
  'style'   => "width:100%",
  'tabindex' => 1
  ));


 $this->setForm($form);
 return parent::_prepareForm();
}

public function getTagalysSyncUrl() {
  return $this->getUrl('*/tagalys/initialSync/');
}


public function getTabLabel() {
  return $this->__('Settings');
}

    /**
   * Tab title getter
   *
   * @return string
   */
    public function getTabTitle() {
     return $this->__('Settings');
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