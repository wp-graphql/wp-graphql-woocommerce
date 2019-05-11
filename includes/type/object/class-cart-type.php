<?php
/**
 * WPObject Type - Cart_Type
 *
 * Registers Cart WPObject type and queries
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.3
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;

/**
 * Class - Cart_Type
 */
class Cart_Type {
	/**
	 * Register Cart-related types and queries to the WPGraphQL schema
	 */
	public static function register() {
		self::register_cart_fee();
		self::register_cart_item();
		self::register_cart();

		register_graphql_field(
			'RootQuery',
			'cart',
			array(
				'type'        => 'Cart',
				'description' => __( 'The cart object', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
					return WC()->cart;
				},
			)
		);

		register_graphql_field(
			'RootQuery',
			'cartItem',
			array(
				'type'        => 'CartItem',
				'args'        => array(
					'key' => array(
						'type' => array( 'non_null' => 'ID' ),
					),
				),
				'description' => __( 'The cart object', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$item = Factory::resolve_cart_item( $args['key'] );
					if ( empty( $item ) ) {
						throw new UserError( __( 'The key input is invalid', 'wp-graphql-woocommerce' ) );
					}

					return $item;
				},
			)
		);

		register_graphql_field(
			'RootQuery',
			'cartFee',
			array(
				'type'        => 'CartFee',
				'args'        => array(
					'id' => array(
						'type' => array( 'non_null' => 'ID' ),
					),
				),
				'description' => __( 'The cart object', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$fee = Factory::resolve_cart_fee( $args['id'] );
					if ( empty( $fee ) ) {
						throw new UserError( __( 'The ID input is invalid', 'wp-graphql-woocommerce' ) );
					}

					return $fee;
				},
			)
		);
	}

	/**
	 * Registers Cart type
	 */
	public static function register_cart() {
		register_graphql_object_type(
			'Cart',
			array(
				'description' => __( 'The cart object', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'subtotal'                => array(
						'type'        => 'Float',
						'description' => __( 'Cart subtotal', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->get_subtotal() ) ? $source->get_subtotal() : 0;
						},
					),
					'subtotalTax'             => array(
						'type'        => 'Float',
						'description' => __( 'Cart subtotal tax', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->get_subtotal_tax() ) ? $source->get_subtotal_tax() : 0;
						},
					),
					'discountTotal'           => array(
						'type'        => 'Float',
						'description' => __( 'Cart discount total', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->get_discount_total() ) ? $source->get_discount_total() : 0;
						},
					),
					'discountTax'             => array(
						'type'        => 'Float',
						'description' => __( 'Cart discount tax', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->get_discount_tax() ) ? $source->get_discount_tax() : 0;
						},
					),
					'shippingTotal'           => array(
						'type'        => 'Float',
						'description' => __( 'Cart shipping total', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->get_shipping_total() ) ? $source->get_shipping_total() : 0;
						},
					),
					'shippingTax'             => array(
						'type'        => 'Float',
						'description' => __( 'Cart shipping tax', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->get_shipping_tax() ) ? $source->get_shipping_tax() : 0;
						},
					),
					'contentsTotal'           => array(
						'type'        => 'Float',
						'description' => __( 'Cart contents total', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->get_cart_contents_total() )
								? $source->get_cart_contents_total()
								: 0;
						},
					),
					'contentsTax'             => array(
						'type'        => 'Float',
						'description' => __( 'Cart contents tax', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->get_cart_contents_tax() )
								? $source->get_cart_contents_tax()
								: 0;
						},
					),
					'feeTotal'                => array(
						'type'        => 'Float',
						'description' => __( 'Cart fee total', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->get_fee_total() ) ? $source->get_fee_total() : 0;
						},
					),
					'feeTax'                  => array(
						'type'        => 'Float',
						'description' => __( 'Cart fee tax', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->get_fee_tax() ) ? $source->get_fee_tax() : 0;
						},
					),
					'total'                   => array(
						'type'        => 'Float',
						'description' => __( 'Cart total after calculation', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							$source->calculate_totals();
							return isset( $source->get_totals()['total'] )
								? apply_filters( 'woocommerce_cart_get_total', $source->get_totals()['total'] )
								: null;
						},
					),
					'totalTax'                => array(
						'type'        => 'Float',
						'description' => __( 'Cart total tax amount', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->get_total_tax() ) ? $source->get_total_tax() : 0;
						},
					),
					'isEmpty'                 => array(
						'type'        => 'Boolean',
						'description' => __( 'Is cart empty', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->is_empty() ) ? $source->is_empty() : null;
						},
					),
					'displayPricesIncludeTax' => array(
						'type'        => 'Boolean',
						'description' => __( 'Do display prices include taxes', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->display_prices_including_tax() )
								? $source->display_prices_including_tax()
								: null;
						},
					),
					'needsShippingAddress'    => array(
						'type'        => 'Boolean',
						'description' => __( 'Is customer shipping address needed', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! is_null( $source->needs_shipping_address() )
								? $source->needs_shipping_address()
								: null;
						},
					),
					'fees'                    => array(
						'type'        => array( 'list_of' => 'CartFee' ),
						'description' => __( 'Additional fees on the cart.', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							$fees = $source->get_fees();
							return ! empty( $fees ) ? array_values( $fees ) : null;
						},
					),
				),
			)
		);
	}

	/**
	 * Registers CartItem type
	 */
	public static function register_cart_item() {
		register_graphql_object_type(
			'CartItem',
			array(
				'description' => __( 'A item in the cart', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'key'         => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'CartItem ID', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $source['key'] ) ? $source['key'] : null;
						},
					),
					'product'     => array(
						'type'        => 'Product',
						'description' => __( 'A product in the cart', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $source['product_id'] )
								? Factory::resolve_crud_object( $source['product_id'], $context )
								: null;
						},
					),
					'variation'   => array(
						'type'        => 'ProductVariation',
						'description' => __( 'Selected variation of the product', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $source['variation_id'] )
								? Factory::resolve_crud_object( $source['variation_id'], $context )
								: null;
						},
					),
					'quantity'    => array(
						'type'        => 'Int',
						'description' => __( 'Quantity of the product', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return isset( $source['quantity'] ) ? absint( $source['quantity'] ) : null;
						},
					),
					'subtotal'    => array(
						'type'        => 'Float',
						'description' => __( 'Item\'s subtotal', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return isset( $source['line_subtotal'] ) ? floatval( $source['line_subtotal'] ) : null;
						},
					),
					'subtotalTax' => array(
						'type'        => 'Float',
						'description' => __( 'Item\'s subtotal tax', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return isset( $source['line_subtotal_tax'] ) ? floatval( $source['line_subtotal_tax'] ) : null;
						},
					),
					'total'       => array(
						'type'        => 'Float',
						'description' => __( 'Item\'s total', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return isset( $source['line_total'] ) ? floatval( $source['line_total'] ) : null;
						},
					),
					'tax'         => array(
						'type'        => 'Float',
						'description' => __( 'Item\'s tax', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return isset( $source['line_tax'] ) ? floatval( $source['line_tax'] ) : null;
						},
					),
				),
			)
		);
	}

	/**
	 * Registers CartFee type
	 */
	public static function register_cart_fee() {
		register_graphql_object_type(
			'CartFee',
			array(
				'description' => __( 'An additional fee', 'wp-graphql-woocommerce' ),
				'fields'       => array(
					'id'       => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'Fee ID', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $source->id ) ? $source->id : null;
						},
					),
					'name'     => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Fee name', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $source->name ) ? $source->name : null;
						},
					),
					'taxClass' => array(
						'type'        => 'TaxClassEnum',
						'description' => __( 'Fee tax class', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $source->tax_class ) ? $source->tax_class : null;
						},
					),
					'taxable'  => array(
						'type'        => 'Boolean',
						'description' => __( 'Is fee taxable?', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $source->taxable ) ? $source->taxable : null;
						},
					),
					'amount'   => array(
						'type'        => 'Float',
						'description' => __( 'Fee amount', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $source->amount ) ? $source->amount : null;
						},
					),
					'total'    => array(
						'type'        => 'Float',
						'description' => __( 'Fee total', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $source->total ) ? $source->total : null;
						},
					),
				),
			)
		);
	}
}
