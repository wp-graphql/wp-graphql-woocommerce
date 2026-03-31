<?php
/**
 * DataLoader - WC_Cart_Item_Loader
 *
 * Loads Models for WooCommerce Shipping Methods defined in custom DB tables.
 *
 * @package WPGraphQL\WooCommerce\Data\Loader
 * @since 1.0.0
 */

namespace WPGraphQL\WooCommerce\Data\Loader;

/**
 * Class WC_Cart_Item_Loader
 */
class WC_Cart_Item_Loader extends WC_Db_Loader {
	/**
	 * WC_Cart_Item_Loader constructor
	 *
	 * @param \WPGraphQL\AppContext $context AppContext instance.
	 */
	public function __construct( $context ) {
		parent::__construct( $context, 'CART_ITEM' );
	}
}
