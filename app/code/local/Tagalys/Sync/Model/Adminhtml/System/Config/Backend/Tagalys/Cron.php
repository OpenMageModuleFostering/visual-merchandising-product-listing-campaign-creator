<?php

class Tagalys_Sync_Model_Adminhtml_System_Config_Backend_Tagalys_Cron extends Mage_Core_Model_Config_Data
{
	const CRON_STRING_PATH = 'crontab/jobs/tagalys_updates_cron/schedule/cron_expr';
	const CRON_MODEL_PATH   = 'crontab/jobs/tagalys_updates_cron/run/model';

	const CRON_FEED_STRING_PATH = 'crontab/jobs/tagalys_resync_cron/schedule/cron_expr';
	const CRON_FEED_MODEL_PATH   = 'crontab/jobs/tagalys_resync_cron/run/model';

	public function setCron()
	{

		$core_helper = Mage::helper('tagalys_core');

		$update_frequency = $core_helper->getTagalysConfig("tagalys_updates_cron_time"); 
		$resync_frequency = $core_helper->getTagalysConfig("feed_cron_time"); 


		try {
			Mage::getModel('core/config_data')
			->load(self::CRON_STRING_PATH, 'path')
			->setValue($update_frequency)
			->setPath(self::CRON_STRING_PATH)
			->save();

			Mage::getModel('core/config_data')
			->load(self::CRON_FEED_STRING_PATH, 'path')
			->setValue($resync_frequency)
			->setPath(self::CRON_FEED_STRING_PATH)
			->save();

		}
		catch (Exception $e) {
			throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));

		}
	}
}