<?php
/**
 * DataLoader - WC_Shipping_Method_Loader
 *
 * Loads Models for WooCommerce Shipping Methods defined in custom DB tables.
 *
 * @package WPGraphQL\WooCommerce\Data\Loader
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Data\Loader;

/**
 * Class WC_Shipping_Method_Loader
 */
class WC_Shipping_Method_Loader extends WC_Db_Loader {
    /**
	 * WC_Shipping_Method_Loader constructor
	 *
	 * @param \WPGraphQL\AppContext $context      AppContext instance.
	 */
    public function __construct( $context ) {
        parent::__construct( $context, 'SHIPPING_METHOD' );
    }
}