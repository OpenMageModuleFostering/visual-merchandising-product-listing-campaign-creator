<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Edit_Tab_Setup extends Mage_Adminhtml_Block_Widget_Form
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
		$fieldset = $form->addFieldset('tagalys_sync_fieldset', array('legend' => $this->__('Product Catalog Store Settings')));

   $fieldset->addField('note_ss', 'note', array(
                       'text'     => $this->__('<small>NOTICE : Please make you disable default magento auto-complete for this to appear on your frontend. </small><br>'),
                       ));


   $stores = Mage::helper("sync/data")->getAllWebsiteStores();
   $fieldset->addField('stores_setup', 'multiselect', array(
                       'label'     => $this->__('Select Store'),
                       'class'     => 'required-entry',
                       'required'  => true,
                       'name'      => 'stores_setup',
                       'style'   => "width:100%",
                       'onclick' => "return false;",
                       'onchange' => "return false;",
                       'value'  => 'default',
                       'value'  => Mage::helper("sync/data")->getSelectedStore(),
                       'values' => $stores,
                       'disabled' => false,
                       'readonly' => false,
                       'after_element_html' => '<small>Select appropriate store which you is being used and you would like to sync.</small>',
                       'tabindex' => 1
                       ));
   if(Mage::helper('tagalys_core')->getTagalysConfig("setup_complete")) {
    $fieldset->addField('tagalys_updates_cron_time', 'select', array(
                        'label'     => $this->__('Catalog Sync Frequency'),
                        'required'  => true,
                        'name'      => 'tagalys_updates_cron_time',
                        'options'   => array(
                                             '' => '',
                                             '*/1 * * * *' => $this->__('Every 1 Minute'),
                                             '*/5 * * * *' => $this->__('Every 5 Minutes'),
                                             '*/10 * * * *' => $this->__('Every 10 Minutes'),
                                             '*/30 * * * *' => $this->__('Every 30 Minutes'),
                                             '0 * * * *' => $this->__('Every 60 Minutes'),
                                             ),
                        'value'     => $this->_helper->getTagalysConfig("tagalys_updates_cron_time"),
                        'required'  => true,
                        'style'   => "width:100%",
        // 'readonly' => false,
                        'after_element_html' => '<small>Please select how often you would like to sync your incremental updates with us.</small>'
                        ));

$dateFormatIso = Mage::app()->getLocale()->getTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

$fieldset->addField('feed_cron_time', 'time', array(
                    'label'     => $this->__('Feed Cron Time'),
                    'class'     => 'required-entry',
                    'required'  => true,
                    'name'      => 'feed_cron_time',
                    'value'  =>  date("h,i,s a", Mage::getModel('core/date')->timestamp(time())),
                    'disabled' => false,
                    'readonly' => false,
                    'style'   => "width:30%",
                    'after_element_html' => '<small> This time is specific to your magento timezone setup <br> Your magento timezone is  <em><b>'.Mage::getStoreConfig('general/locale/timezone'). '</b></em><br> We recommend you to select a low traffic time period </small>',
                    'tabindex' => 1
                    ));

$fieldset->addField('note_cron', 'note', array(
                    'text'     => $this->__('<small>NOTICE : Please make sure cron is setup and running.</small><br>
                                            REF: <a target=_blank href="http://devdocs.magento.com/guides/m1x/install/installing_install.html#install-cron">Magento Cron Documentation</a>'),
                    ));

$fieldset->addField('submit_config', 'submit', array(
                    'name' => 'submit_config',
                    'value' => 'Save settings',
                    'class'=> "tagalys-btn",
                    'disabled' => false,
                    'style'   => "width:100%",
                    'after_element_html' => '<small><em></em></small>',
                    'tabindex' => 1
                    ));
} else {
 $fieldset->addField('note_cron', 'note', array(
                     'text'     => $this->__('<small>NOTICE : Please make sure cron is setup and running.</small><br>
                                             REF: <a target=_blank href="http://devdocs.magento.com/guides/m1x/install/installing_install.html#install-cron">Magento Cron Documentation</a>'),
                     ));

 $fieldset->addField('checkbox', 'checkbox', array(
                     'name'      => 'Checkbox',
                     'checked' => false,
                     'onclick'   => 'this.value = this.checked ? 1 : 0;',
                     'disabled' => false,
                     'after_element_html' => '<small>I confirm to start product catalog sync for the above selected stores.<br><em>We recommend you to do this at low traffic hours.</em><br><em>Please enable the checkbox to Submit and start catalog sync.</em></small>',
                     'tabindex' => 1
                     ));

 $fieldset->addField('submit', 'submit', array(
                     'name' => 'submit_config',
                     'value' => 'Submit & Start Catalog Sync',
                     'class'=> "tagalys-btn",
                     'disabled' => true,
                     'style'   => "width:100%",
                     'after_element_html' => '<small><em></em></small>',
                     'tabindex' => 1
                     ));

}



$this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap("{$htmlIdPrefix}sync_level", 'sync_level')
                ->addFieldMap("{$htmlIdPrefix}note_advanced", 'note_advanced')
                ->addFieldMap("{$htmlIdPrefix}checkbox", 'checkbox')
                ->addFieldMap("{$htmlIdPrefix}submit", 'submit')
                ->addFieldDependence('note_advanced', 'sync_level', 'advanced')
      // ->addFieldDependence('submit', 'checkbox', '1')
                );

$this->setForm($form);
return parent::_prepareForm();
}


public function getTabLabel() {
  return $this->__('Catalog Store Setup');
}

    /**
   * Tab title getter
   *
   * @return string
   */
    public function getTabTitle() {
      return $this->__('Catalog Store Setup');
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