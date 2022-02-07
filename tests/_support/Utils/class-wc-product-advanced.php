<?php
/**
 * Advanced Product Type
 */
class WC_Product_Advanced extends \WC_Product_Simple {
	/**
	 * Return the product type
	 *
	 * @return string
	 */
	public function get_type() {
		return 'advanced';
	}
}
