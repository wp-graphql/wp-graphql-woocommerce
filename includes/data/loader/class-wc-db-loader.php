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
use WPGraphQL\AppContext;
use WPGraphQL\Data\Loader\AbstractDataLoader;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\Model\Shipping_Method;
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
	 * @throws \Exception Invalid loader type.
	 */
	public function loadKeys( array $keys ) {
		$loader = null;
		switch ( $this->loader_type ) {
			case 'CART_ITEM':
				$loader = [ $this, 'load_cart_item_from_key' ];
				break;
			case 'DOWNLOADABLE_ITEM':
				$loader = [ $this, 'load_downloadable_item_from_id' ];
				break;
			case 'TAX_RATE':
				$loader = [ $this, 'load_tax_rate_from_id' ];
				break;
			case 'ORDER_ITEM':
				$loader = [ $this, 'load_order_item_from_id' ];
				break;
			case 'SHIPPING_METHOD':
				$loader = [ $this, 'load_shipping_method_from_id' ];
				break;
			default:
				/**
				 * For adding custom key types to this loader
				 *
				 * @param callable|null $loader       Callback that gets entry from key.
				 * @param string        $loader_type  Used to determine loader being used for this instance of the loader.
				 */
				$loader = apply_filters( 'woographql_db_loader_func', null, $this->loader_type );

				if ( empty( $loader ) ) {
					throw new \Exception(
						/* translators: %s: Loader Type */
						sprintf( __( 'Loader type invalid: %s', 'wp-graphql-woocommerce' ), $this->loader_type )
					);
				}
		}//end switch

		$loaded_items = [];

		/**
		 * Loop over the keys and return an array of items.
		 */
		foreach ( $keys as $key ) {
			$loaded_items[ $key ] = call_user_func( $loader, $key );
		}

		return ! empty( $loaded_items ) ? $loaded_items : [];
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
	 * @return \WC_Customer_Download|null
	 */
	public function load_downloadable_item_from_id( $id ) {
		$node = new \WC_Customer_Download( $id );
		return 0 === $node->get_id() ? $node : null;
	}

	/**
	 * Returns the tax rate connected the provided IDs.
	 *
	 * @param int $id - Tax rate IDs.
	 *
	 * @return Tax_Rate|null
	 */
	public function load_tax_rate_from_id( $id ) {
		global $wpdb;

		/**
		 * Get tax rate from WooCommerce.
		 *
		 * @var object{
		 *  tax_rate_id: int,
		 *  tax_rate_class: string,
		 *  tax_rate_country: string,
		 *  tax_rate_state: string,
		 *  tax_rate: string,
		 *  tax_rate_name: string,
		 *  tax_rate_priority: int,
		 *  tax_rate_compound: bool,
		 *  tax_rate_shipping: bool,
		 *  tax_rate_order: int,
		 *  tax_rate_city: string,
		 *  tax_rate_postcode: string
		 *  } $rate
		 */
		$rate = \WC_Tax::_get_tax_rate( $id, OBJECT );
		if ( ! empty( $rate ) && is_object( $rate ) ) {
			// Get locales from a tax rate.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
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
					$rate->{'tax_rate_' . $locale->location_type} = [];
				}
				$rate->{'tax_rate_' . $locale->location_type}[] = $locale->location_code;
			}
			return new Tax_Rate( $rate );
		} else {
			return null;
		}//end if
	}

	/**
	 * Returns the shipping method Model for the shipping method ID.
	 *
	 * @param int $id - Shipping method ID.
	 *
	 * @return Shipping_Method
	 * @access public
	 * @throws UserError Invalid object.
	 */
	public function load_shipping_method_from_id( $id ) {
		$wc_shipping = \WC_Shipping::instance();
		$methods     = $wc_shipping->get_shipping_methods();
		if ( empty( $methods[ $id ] ) ) {
			throw new UserError(
			/* translators: shipping method ID */
				sprintf( __( 'No Shipping Method assigned to ID %s was found ', 'wp-graphql-woocommerce' ), $id )
			);
		}

		$method = $methods[ $id ];

		return new Shipping_Method( $method );
	}

	/**
	 * Returns the order item connected the provided IDs.
	 *
	 * @param int $id - Order item IDs.
	 *
	 * @return \WPGraphQL\WooCommerce\Model\Order_Item|null
	 */
	public function load_order_item_from_id( $id ) {
		$item = \WC()->order_factory::get_order_item( $id );

		if ( false === $item ) {
			return null;
		}

		$item = new \WPGraphQL\WooCommerce\Model\Order_Item( $item );

		return $item;
	}
}
