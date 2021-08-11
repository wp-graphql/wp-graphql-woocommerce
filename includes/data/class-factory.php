<?php
/**
 * Factory
 *
 * This class serves as a factory for all the resolvers of queries and mutations.
 *
 * @package WPGraphQL\WooCommerce\Data
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Data;

use GraphQL\Deferred;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use function WC;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Model\Order_Item;
use WPGraphQL\WooCommerce\Model\Product;
use WPGraphQL\WooCommerce\Model\Customer;
use WPGraphQL\WooCommerce\Model\Tax_Rate;
use WPGraphQL\WooCommerce\Model\Shipping_Method;

/**
 * Class Factory
 */
class Factory {
	/**
	 * Returns the current woocommerce customer object tied to the current session.
	 *
	 * @return Customer
	 * @access public
	 */
	public static function resolve_session_customer() {
		return new Customer();
	}

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
		$loader->buffer( array( $customer_id ) );
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

		$context->getLoader( 'wc_post' )->buffer( array( $id ) );
		return new Deferred(
			function () use ( $id, $context ) {
				return $context->getLoader( 'wc_post' )->load( $id );
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
	 * @param string     $id - Tax rate ID.
	 * @param AppContext $context - AppContext object.
	 *
	 * @return Deferred object
	 */
	public static function resolve_tax_rate( $id, AppContext $context ) {
		if ( empty( $id ) || ! is_numeric( $id ) ) {
			return null;
		}

		$id     = absint( $id );
		$loader = $context->getLoader( 'tax_rate' );
		$loader->buffer( array( $id ) );
		return new Deferred(
			function () use ( $loader, $id ) {
				return $loader->load( $id );
			}
		);
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
				/* translators: shipping method ID */
				sprintf( __( 'No Shipping Method assigned to ID %s was found ', 'wp-graphql-woocommerce' ), $id )
			);
		}

		$method = $methods[ $id ];

		return new Shipping_Method( $method );
	}

	/**
	 * Resolves the WooCommerce cart instance.
	 *
	 * @return \WC_Cart
	 */
	public static function resolve_cart() {
		return WC()->cart;
	}

	/**
	 * Resolves a cart item by key.
	 *
	 * @param string     $key      Cart item key.
	 * @param AppContext $context  AppContext object.
	 *
	 * @return Deferred object
	 */
	public static function resolve_cart_item( $key, AppContext $context ) {
		if ( empty( $key ) ) {
			return null;
		}

		$context->getLoader( 'cart_item' )->buffer( array( $key ) );
		return new Deferred(
			function () use ( $key, $context ) {
				return $context->getLoader( 'cart_item' )->load( $key );
			}
		);
	}

	/**
	 * Resolves a fee object by ID.
	 *
	 * @param int $id Fee object generated ID.
	 *
	 * @return object
	 */
	public static function resolve_cart_fee( $id ) {
		if ( ! empty( self::resolve_cart()->get_fees()[ $id ] ) ) {
			return self::resolve_cart()->get_fees()[ $id ];
		}

		return null;
	}

	/**
	 * Resolves a downloadable item by ID.
	 *
	 * @param int        $id       Downloadable item ID.
	 * @param AppContext $context  AppContext object.
	 *
	 * @return Deferred object
	 */
	public static function resolve_downloadable_item( $id, AppContext $context ) {
		if ( empty( $id ) || ! absint( $id ) ) {
			return null;
		}
		$object_id = absint( $id );
		$loader    = $context->getLoader( 'downloadable_item' );
		$loader->buffer( array( $object_id ) );
		return new Deferred(
			function () use ( $loader, $object_id ) {
				return $loader->load( $object_id );
			}
		);
	}

	/**
	 * Resolves Relay node for some WooGraphQL types.
	 *
	 * @param mixed      $node     Node object.
	 * @param string     $id       Object unique ID.
	 * @param string     $type     Node type.
	 * @param AppContext $context  AppContext instance.
	 *
	 * @return mixed
	 */
	public static function resolve_node( $node, $id, $type, $context ) {
		switch ( $type ) {
			case 'customer':
				$node = self::resolve_customer( $id, $context );
				break;
			case 'shop_coupon':
			case 'shop_order':
			case 'shop_order_refund':
			case 'product':
			case 'product_variation':
				$node = self::resolve_crud_object( $id, $context );
				break;
			case 'shipping_method':
				$node = self::resolve_shipping_method( $id );
				break;
			case 'tax_rate':
				$node = self::resolve_tax_rate( $id, $context );
				break;
		}

		return $node;
	}

	/**
	 * Resolves Relay node type for some WooGraphQL types.
	 *
	 * @param string|null $type  Node type.
	 * @param mixed       $node  Node object.
	 *
	 * @return string|null
	 */
	public static function resolve_node_type( $type, $node ) {
		switch ( true ) {
			case is_a( $node, Coupon::class ):
				$type = 'Coupon';
				break;
			case is_a( $node, Customer::class ):
				$type = 'Customer';
				break;
			case is_a( $node, Order::class ):
				$type = 'Order';
				break;
			case is_a( $node, Product::class ) && 'simple' === $node->type:
				$type = 'SimpleProduct';
				break;
			case is_a( $node, Product::class ) && 'variable' === $node->type:
				$type = 'VariableProduct';
				break;
			case is_a( $node, Product::class ) && 'external' === $node->type:
				$type = 'ExternalProduct';
				break;
			case is_a( $node, Product::class ) && 'grouped' === $node->type:
				$type = 'GroupProduct';
				break;
			case is_a( $node, Product_Variation::class ):
				$type = 'ProductVariation';
				break;
			case is_a( $node, Refund::class ):
				$type = 'Refund';
				break;
			case is_a( $node, Shipping_Method::class ):
				$type = 'ShippingMethod';
				break;
			case is_a( $node, Tax_Rate::class ):
				$type = 'TaxRate';
				break;
		}

		return $type;
	}
}
