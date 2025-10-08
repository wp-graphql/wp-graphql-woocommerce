<?php
/**
 * DataLoader - WC_Shipping_Zone_Loader
 *
 * Loads Models for WooCommerce Shipping Zones defined in custom DB tables.
 *
 * @package WPGraphQL\WooCommerce\Data\Loader
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Data\Loader;

/**
 * Class WC_Shipping_Zone_Loader
 */
class WC_Shipping_Zone_Loader extends WC_Db_Loader {
    /**
	 * WC_Shipping_Zone_Loader constructor
	 *
	 * @param \WPGraphQL\AppContext $context      AppContext instance.
	 */
    public function __construct( $context ) {
        parent::__construct( $context, 'SHIPPING_ZONE' );
    }
}