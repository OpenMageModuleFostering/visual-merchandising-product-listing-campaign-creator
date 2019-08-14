<?php
class Tagalys_Sync_Helper_Inventory extends Mage_Core_Helper_Abstract {

	public function getInventoryStockItemField() {
				$stock_item = array(
                "qty" => "Qty",
                "min_qty" => "Minimum Qty Allowed in Shopping Cart",
                "use_config_min_qty" => "Minimum Qty Allowed in Shopping Cart Use Config",
                "is_qty_decimal" => "Qty Uses Decimals",
                "backorders" => "Backorders",
                "use_config_backorders" => "Backorders Use Config Settings",
                "min_sale_qty" => "",
                "use_config_min_sale_qty" => "",
                "max_sale_qty" => "Maximum Qty Allowed in Shopping Cart",
                "use_config_max_sale_qty" => "Maximum Qty Allowed in Shopping Cart Use Config Settings",
                "is_in_stock" => "Stock Availability",
                "low_stock_date" => "",
                "notify_stock_qty" => "Notify for Quantity Below",
                "use_config_notify_stock_qty" => "Notify for Quantity Below Use Config Settings",
                "manage_stock" => "Manage Stock",
                "use_config_manage_stock" => "Manage Stock Use Config Settings",
                "stock_status_changed_auto" => "",
                "use_config_qty_increments" => "",
                "qty_increments" => "Enable Qty Increments",
                "use_config_enable_qty_inc" => "Enable Qty Increments Use Config Settings",
                "enable_qty_increments" => "Enable Qty Increments",
                "is_decimal_divided" => "", 
                "stock_status_changed_automatically" => "",
                "use_config_enable_qty_increments" => "Enable Qty Use Config Settings"
            );
		return $stock_item;
		
	}	

}
