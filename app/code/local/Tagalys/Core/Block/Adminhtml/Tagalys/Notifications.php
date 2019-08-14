<?php

class Tagalys_Core_Block_Adminhtml_Tagalys_Notifications extends Mage_Adminhtml_Block_Template
{
  public function _toHtml($className = "notification-global")
  {
        // Let other extensions add messages
    $html = null;
    Mage::dispatchEvent('tagalys_notifications_before');
    if(Mage::helper('tagalys_core')->getTagalysConfig('is_resync_needed')) {
      $message = "Please manually resync your products at Tagalys -> Configuration -> Catalog Sync Status -> Manual Resync";
      $html .= "<div class='$className'><strong class='label'>Tagalys Notice:</strong>" . $message . "</div>";
    }

    return $html;
  }
}