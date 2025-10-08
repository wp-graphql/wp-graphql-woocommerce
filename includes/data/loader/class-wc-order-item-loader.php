<?php
/**
 * DataLoader - WC_Order_Item_Loader
 *
 * Loads Models for WooCommerce Order Items defined in custom DB tables.
 *
 * @package WPGraphQL\WooCommerce\Data\Loader
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Data\Loader;

/**
 * Class WC_Order_Item_Loader
 */
class WC_Order_Item_Loader extends WC_Db_Loader {
    /**
	 * WC_Order_Item_Loader constructor
	 *
	 * @param \WPGraphQL\AppContext $context AppContext instance.
	 */
    public function __construct( $context ) {
        parent::__construct( $context, 'ORDER_ITEM' );
    }
}