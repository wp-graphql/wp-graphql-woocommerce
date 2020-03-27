<?php
/**
 * DataLoader - WC_Db_Loader
 *
 * Loads Models for WooCommerce objects defined in custom DB tables.
 *
 * @package WPGraphQL\WooCommerce\Data\Loader
 * @since 0.5.0
 */

namespace WPGraphQL\WooCommerce\Data\Loader;

use GraphQL\Deferred;
use GraphQL\Error\UserError;
use WPGraphQL\Data\Loader\AbstractDataLoader;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\Model\Tax_Rate;

/**
 * Class WC_Db_Loader
 */
class WC_Db_Loader extends AbstractDataLoader {
	/**
	 * Stores loaded CPTs.
	 *
	 * @var array
	 */
	protected $loaded_objects;

	/**
	 * Loader type
	 * 
	 * @var string
	 */
	protected $loader_type;
	
	/**
	 * WC_Db_Loader constructor
	 * 
	 * @param AppContext $context      AppContext instance.
	 * @param string     $loader_type  Type of loader be initialized.
	 */
	public function __construct( $context, $loader_type ) {
		$this->loader_type = $loader_type;
		parent::__construct( $context );
	}

    /**
	 * Given array of keys, loads and returns a map consisting of keys from `keys` array and loaded
	 * posts as the values
	 *
	 * @param array $keys - array of IDs.
	 *
	 * @return array
	 * @throws \Exception Invalid loader type
	 */
	public function loadKeys( array $keys ) {
		$loader = null;
		switch ( $this->loader_type ) {
			case 'CART_ITEM':
				$loader = array( $this, 'load_cart_item_from_key' );
				break;
			case 'DOWNLOADABLE_ITEM':
				$loader = array( $this, 'load_downloadable_item_from_id' );
				break;
			case 'TAX_RATE':
				$loader = array( $this, 'load_tax_rate_from_id' );
				break;
			default:
				$loader = apply_filters( 'woographql_db_loader_func', null );
				if ( empty( $loader ) ) {
					throw new \Exception(
						/* translators: %s: Loader Type */
						sprintf( __( 'Loader type invalid: %s', 'wp-graphql-woocommerce' ), $this->loader_type )
					);
				}
		}

		$loaded_items = array();

		/**
		 * Loop over the keys and return an array of items.
		 */
		foreach ( $keys as $key ) {
			$loaded_items[ $key ] = call_user_func( $loader, $key );
		}

		return ! empty( $loaded_items ) ? $loaded_items : array();
	}
	
	/**
	 * Returns the cart item connected the provided key.
	 * 
	 * @param string $key - Cart item key.
	 *
	 * @return array
	 */
	public function load_cart_item_from_key( $key ) {
		// Add the cart item's product and product variation to WC-CPT buffer.
		return Factory::resolve_cart()->get_cart_item( $key );
	}

	/**
	 * Returns the downloadable item connected the provided IDs.
	 * 
	 * @param int $id - Downloadable item ID.
	 * 
	 * @return WC_Customer_Download|null
	 */
	public function load_downloadable_item_from_id( $id ) {
		$node = new \WC_Customer_Download( $id );
		return 0 === $node->get_id() ? $node : null;
	}

	/**
	 * Returns the tax rate connected the provided IDs.
	 * 
	 * @param int $key - Tax rate IDs.
	 * 
	 * @return Tax_Rate|null
	 */
	public function load_tax_rate_from_id( $id ) {
		global $wpdb;

		$rate = \WC_Tax::_get_tax_rate( $id, OBJECT );
		if ( ! \is_wp_error( $rate ) && ! empty( $rate ) ) {
			// Get locales from a tax rate.
			$locales = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT location_code, location_type
					FROM {$wpdb->prefix}woocommerce_tax_rate_locations
					WHERE tax_rate_id = %d",
					$rate->tax_rate_id
				)
			);

			foreach ( $locales as $locale ) {
				if ( empty( $rate->{'tax_rate_' . $locale->location_type} ) ) {
					$rate->{'tax_rate_' . $locale->location_type} = array();
				}
				$rate->{'tax_rate_' . $locale->location_type}[] = $locale->location_code;
			}
			return new Tax_Rate( $rate );
		} else {
			return null;
		}
	}
}
