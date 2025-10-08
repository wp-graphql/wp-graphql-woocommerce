<?php
/**
 * DataLoader - WC_Tax_Class_Loader
 *
 * Loads Models for WooCommerce Tax Classes defined in custom DB tables.
 *
 * @package WPGraphQL\WooCommerce\Data\Loader
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Data\Loader;

/**
 * Class WC_Tax_Class_Loader
 */
class WC_Tax_Class_Loader extends WC_Db_Loader {
    /**
	 * WC_Tax_Class_Loader constructor
	 *
	 * @param \WPGraphQL\AppContext $context AppContext instance.
	 */
    public function __construct( $context ) {
        parent::__construct( $context, 'TAX_CLASS' );
    }
}