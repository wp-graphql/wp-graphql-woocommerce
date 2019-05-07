<?php
/**
 * Factory
 *
 * This class serves as a factory for all the resolvers of queries and mutations.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Data;

use GraphQL\Deferred;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Coupon_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Customer_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Order_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Order_Item_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Product_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Product_Attribute_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Variation_Attribute_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Product_Download_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Refund_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Tax_Rate_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Shipping_Method_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\Cart_Item_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Model\Order_Item;
use WPGraphQL\Extensions\WooCommerce\Model\Tax_Rate;
use WPGraphQL\Extensions\WooCommerce\Model\Shipping_Method;

/**
 * Class Factory
 */
class Factory {
	/**
	 * Returns the Customer store object for the provided user ID
	 *
	 * @param int        $id      - user ID of the customer being retrieved.
	 * @param AppContext $context - AppContext object.
	 *
	 * @return Deferred object
	 * @access public
	 */
	public static function resolve_customer( $id, AppContext $context ) {
		if ( empty( $id ) || ! absint( $id ) ) {
			return null;
		}
		$customer_id = absint( $id );
		$loader      = $context->getLoader( 'wc_customer' );
		$loader->buffer( [ $customer_id ] );
		return new Deferred(
			function () use ( $loader, $customer_id ) {
				return $loader->load( $customer_id );
			}
		);
	}

	/**
	 * Returns the WooCommerce CRUD object for the post ID
	 *
	 * @param int        $id      - post ID of the crud object being retrieved.
	 * @param AppContext $context - AppContext object.
	 *
	 * @return Deferred object
	 * @access public
	 */
	public static function resolve_crud_object( $id, AppContext $context ) {
		if ( empty( $id ) || ! absint( $id ) ) {
			return null;
		}
		$object_id = absint( $id );
		$loader    = $context->getLoader( 'wc_post_crud' );
		$loader->buffer( [ $object_id ] );
		return new Deferred(
			function () use ( $loader, $object_id ) {
				return $loader->load( $object_id );
			}
		);
	}

	/**
	 * Returns the order item Model for the order item.
	 *
	 * @param int $item - order item crud object instance.
	 *
	 * @return Order_Item
	 * @access public
	 * @throws UserError Invalid object.
	 */
	public static function resolve_order_item( $item ) {
		/**
		 * If $id is an instance of WC_Order_Item
		 */
		if ( is_a( $item, \WC_Order_Item::class ) ) {
			return new Order_Item( $item );
		} else {
			throw new UserError( __( 'Object provided to order item resolver is an invalid type', 'wp-graphql-woocommerce' ) );
		}
	}

	/**
	 * Returns the tax rate Model for the tax rate ID.
	 *
	 * @param int $id - Tax rate ID.
	 *
	 * @return Tax_Rate
	 * @access public
	 * @throws UserError Invalid object.
	 */
	public static function resolve_tax_rate( $id ) {
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
			throw new UserError(
				/* translators: tax rate not found error message */
				sprintf( __( 'No Tax Rate assigned to ID %s was found ', 'wp-graphql-woocommerce' ), $id )
			);
		}
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
	public static function resolve_shipping_method( $id ) {
		$wc_shipping = \WC_Shipping::instance();
		$methods     = $wc_shipping->get_shipping_methods();
		if ( empty( $methods[ $id ] ) ) {
			throw new UserError(
				/* translators: shipping method not found error message */
				sprintf( __( 'No Shipping Method assigned to ID %s was found ', 'wp-graphql-woocommerce' ), $id )
			);
		}

		$method = $methods[ $id ];

		return new Shipping_Method( $method );
	}

	/**
	 * Resolves a cart item by key.
	 *
	 * @param string $id cart item key.
	 *
	 * @return object
	 */
	public static function resolve_cart_item( $id ) {
		$item = WC()->cart->get_cart_item( $id );

		return $item;
	}

	/**
	 * Resolves a fee object by ID.
	 *
	 * @param int $id Fee object generated ID.
	 *
	 * @return object
	 */
	public static function resolve_cart_fee( $id ) {
		$fees = WC()->cart->get_fees();

		if ( ! empty( $fees[ $id ] ) ) {
			return $fees[ $id ];
		}

		return null;
	}

	/**
	 * Resolves Coupon connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_coupon_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Coupon_Connection_Resolver( $source, $args, $context, $info );
		return $resolver->get_connection();
	}

	/**
	 * Resolves Customer connections
	 *
	 * @param mixed       $source     - Connection parent resolver.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_customer_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Customer_Connection_Resolver( $source, $args, $context, $info );
		return $resolver->get_connection();
	}

	/**
	 * Resolves Order connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_order_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Order_Connection_Resolver( $source, $args, $context, $info );
		return $resolver->get_connection();
	}

	/**
	 * Resolves Order connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_order_item_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Order_Item_Connection_Resolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Resolves Product connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_product_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );
		return $resolver->get_connection();
	}

	/**
	 * Resolves ProductAttribute connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_product_attribute_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Product_Attribute_Connection_Resolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Resolves VariationAttribute connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_variation_attribute_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Variation_Attribute_Connection_Resolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Resolves ProductDownload connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_product_download_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Product_Download_Connection_Resolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Resolves Refund connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_refund_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Refund_Connection_Resolver( $source, $args, $context, $info );
		return $resolver->get_connection();
	}

	/**
	 * Resolves TaxRate connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_tax_rate_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Tax_Rate_Connection_Resolver( $source, $args, $context, $info );
		return $resolver->get_connection();
	}

	/**
	 * Resolves ShippingMethod connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_shipping_method_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Shipping_Method_Connection_Resolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Resolves CartItem connections
	 *
	 * @param mixed       $source     - Data resolver for connection source.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return array
	 * @access public
	 */
	public static function resolve_cart_item_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$resolver = new Cart_Item_Connection_Resolver();
		return $resolver->resolve( $source, $args, $context, $info );
	}
}
