<?php
/**
 * WPObject Type - Cart_Type
 *
 * Registers Cart WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.3
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Connection\Cart_Item_Connection_Resolver;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class - Cart_Type
 */
class Cart_Type {
	/**
	 * Register Cart-related types and queries to the WPGraphQL schema
	 *
	 * @return void
	 */
	public static function register() {
		self::register_cart_fee();
		self::register_cart_tax();
		self::register_applied_coupon();
		self::register_cart();
	}

	/**
	 * Returns the "Cart" type fields.
	 *
	 * @param array $other_fields Extra fields configs to be added or override the default field definitions.
	 *
	 * @return array
	 */
	public static function get_cart_fields( $other_fields = [] ) {
		return array_merge(
			[
				'subtotal'                 => [
					'type'        => 'String',
					'description' => __( 'Cart subtotal', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args ) {
						$price = ! is_null( $source->get_subtotal() ) ? $source->get_subtotal() : 0;

						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							return $price;
						}

						return wc_graphql_price( $price );
					},
				],
				'subtotalTax'              => [
					'type'        => 'String',
					'description' => __( 'Cart subtotal tax', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args ) {
						$price = ! is_null( $source->get_subtotal_tax() ) ? $source->get_subtotal_tax() : 0;

						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							return $price;
						}

						return wc_graphql_price( $price );
					},
				],
				'discountTotal'            => [
					'type'        => 'String',
					'description' => __( 'Cart discount total', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args ) {
						$price = ! is_null( $source->get_discount_total() ) ? $source->get_discount_total() : 0;

						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							return $price;
						}

						return wc_graphql_price( $price );
					},
				],
				'discountTax'              => [
					'type'        => 'String',
					'description' => __( 'Cart discount tax', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args ) {
						$price = ! is_null( $source->get_discount_tax() ) ? $source->get_discount_tax() : 0;

						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							return $price;
						}

						return wc_graphql_price( $price );
					},
				],
				'availableShippingMethods' => [
					'type'        => [ 'list_of' => 'ShippingPackage' ],
					'description' => __( 'Available shipping methods for this order.', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source ) {
						$packages = [];

						$available_packages = $source->needs_shipping()
							? \WC()->shipping()->calculate_shipping( $source->get_shipping_packages() )
							: [];

						foreach ( $available_packages as $index => $package ) {
							$package['index'] = $index;
							$packages[]       = $package;
						}

						return $packages;
					},
				],
				'chosenShippingMethods'    => [
					'type'        => [ 'list_of' => 'String' ],
					'description' => __( 'Shipping method chosen for this order.', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source ) {
						$chosen_shipping_methods = [];

						$available_packages = $source->needs_shipping()
							? \WC()->shipping()->calculate_shipping( $source->get_shipping_packages() )
							: [];

						foreach ( $available_packages as $i => $package ) {
							if ( isset( \WC()->session->chosen_shipping_methods[ $i ] ) ) {
								$chosen_shipping_methods[] = \WC()->session->chosen_shipping_methods[ $i ];
							}
						}

						return $chosen_shipping_methods;
					},
				],
				'shippingTotal'            => [
					'type'        => 'String',
					'description' => __( 'Cart shipping total', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args ) {
						$price = ! is_null( $source->get_shipping_total() ) ? $source->get_shipping_total() : 0;

						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							return $price;
						}

						return wc_graphql_price( $price );
					},
				],
				'shippingTax'              => [
					'type'        => 'String',
					'description' => __( 'Cart shipping tax', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args ) {
						$price = ! is_null( $source->get_shipping_tax() ) ? $source->get_shipping_tax() : 0;

						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							return $price;
						}

						return wc_graphql_price( $price );
					},
				],
				'contentsTotal'            => [
					'type'        => 'String',
					'description' => __( 'Cart contents total', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args ) {
						if ( $source->display_prices_including_tax() ) {
							$cart_subtotal = $source->get_subtotal() + $source->get_subtotal_tax();
						} else {
							$cart_subtotal = $source->get_subtotal();
						}

						$price = ! is_null( $cart_subtotal )
							? $cart_subtotal
							: 0;

						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							return $price;
						}

						return wc_graphql_price( $price );
					},
				],
				'contentsTax'              => [
					'type'        => 'String',
					'description' => __( 'Cart contents tax', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args ) {
						$price = ! is_null( $source->get_cart_contents_tax() )
							? $source->get_cart_contents_tax()
							: 0;

						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							return $price;
						}

						return wc_graphql_price( $price );
					},
				],
				'feeTotal'                 => [
					'type'        => 'String',
					'description' => __( 'Cart fee total', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args ) {
						$price = ! is_null( $source->get_fee_total() ) ? $source->get_fee_total() : 0;

						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							return $price;
						}

						return wc_graphql_price( $price );
					},
				],
				'feeTax'                   => [
					'type'        => 'String',
					'description' => __( 'Cart fee tax', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args ) {
						$price = ! is_null( $source->get_fee_tax() ) ? $source->get_fee_tax() : 0;

						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							return $price;
						}

						return wc_graphql_price( $price );
					},
				],
				'total'                    => [
					'type'        => 'String',
					'description' => __( 'Cart total after calculation', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args ) {
						$source->calculate_totals();
						$price = isset( $source->get_totals()['total'] )
							? apply_filters( 'graphql_woocommerce_cart_get_total', $source->get_totals()['total'] )
							: 0;

						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							return $price;
						}

						return wc_graphql_price( $price );
					},
				],
				'totalTax'                 => [
					'type'        => 'String',
					'description' => __( 'Cart total tax amount', 'wp-graphql-woocommerce' ),
					'args'        => [
						'format' => [
							'type'        => 'PricingFieldFormatEnum',
							'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
						],
					],
					'resolve'     => static function ( $source, array $args ) {
						$price = ! is_null( $source->get_total_tax() ) ? $source->get_total_tax() : 0;

						if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
							return $price;
						}

						return wc_graphql_price( $price );
					},
				],
				'totalTaxes'               => [
					'type'        => [ 'list_of' => 'CartTax' ],
					'description' => __( 'Cart total taxes itemized', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source ) {
						$taxes = $source->get_tax_totals();
						return ! empty( $taxes ) ? array_values( $taxes ) : null;
					},
				],
				'isEmpty'                  => [
					'type'        => 'Boolean',
					'description' => __( 'Is cart empty', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source ) {
						return ! is_null( $source->is_empty() ) ? $source->is_empty() : null;
					},
				],
				'displayPricesIncludeTax'  => [
					'type'        => 'Boolean',
					'description' => __( 'Do display prices include taxes', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source ) {
						return ! is_null( $source->display_prices_including_tax() )
							? $source->display_prices_including_tax()
							: null;
					},
				],
				'needsShippingAddress'     => [
					'type'        => 'Boolean',
					'description' => __( 'Is customer shipping address needed', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source ) {
						return ! is_null( $source->needs_shipping_address() )
							? $source->needs_shipping_address()
							: null;
					},
				],
				'fees'                     => [
					'type'        => [ 'list_of' => 'CartFee' ],
					'description' => __( 'Additional fees on the cart.', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source ) {
						$fees = $source->get_fees();
						return ! empty( $fees ) ? array_values( $fees ) : null;
					},
				],
				'appliedCoupons'           => [
					'type'        => [ 'list_of' => 'AppliedCoupon' ],
					'description' => __( 'Coupons applied to the cart', 'wp-graphql-woocommerce' ),
					'resolve'     => static function ( $source ) {
						$applied_coupons = $source->get_applied_coupons();

						return ! empty( $applied_coupons ) ? $applied_coupons : null;
					},
				],
			],
			$other_fields
		);
	}

	/**
	 * Returns the "Cart" type connections.
	 *
	 * @param array $other_connections Extra connections configs to be added or override the default connection definitions.
	 * @return array
	 */
	public static function get_cart_connections( $other_connections = [] ) {
		return array_merge(
			[
				'contents' => [
					'toType'           => 'CartItem',
					'connectionArgs'   => [
						'needsShipping' => [
							'type'        => 'Boolean',
							'description' => __( 'Limit results to cart items that require shipping', 'wp-graphql-woocommerce' ),
						],
					],
					'connectionFields' => [
						'itemCount'    => [
							'type'        => 'Int',
							'description' => __( 'Total number of items in the cart.', 'wp-graphql-woocommerce' ),
							'resolve'     => static function ( $source ) {
								if ( empty( $source['edges'] ) ) {
									return 0;
								}

								$items = array_values( $source['edges'][0]['source']->get_cart() );
								if ( empty( $items ) ) {
									return 0;
								}

								return array_sum( array_column( $items, 'quantity' ) );
							},
						],
						'productCount' => [
							'type'        => 'Int',
							'description' => __( 'Total number of different products in the cart', 'wp-graphql-woocommerce' ),
							'resolve'     => static function ( $source ) {
								if ( empty( $source['edges'] ) ) {
									return 0;
								}

								return count( array_values( $source['edges'][0]['source']->get_cart() ) );
							},
						],
					],
					'resolve'          => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Cart_Item_Connection_Resolver( $source, $args, $context, $info );

						return $resolver->get_connection();
					},
				],
			],
			$other_connections
		);
	}

	/**
	 * Registers Cart type
	 *
	 * @return void
	 */
	public static function register_cart() {
		register_graphql_object_type(
			'Cart',
			[
				'description' => __( 'The cart object', 'wp-graphql-woocommerce' ),
				/**
				 * Allows for a decisive filtering of the cart fields.
				 * Note: Only use if deregisteration or renaming the field(s) has failed.
				 *
				 * @param array $fields  Cart field definitions.
				 * @return array
				 */
				'fields'      => apply_filters( 'woographql_cart_field_definitions', self::get_cart_fields() ),
				/**
				 * Allows for a decisive filtering of the cart connections.
				 * Note: Only use if deregisteration or renaming the connection(s) has failed.
				 *
				 * @param array $connections  Cart connection definitions.
				 * @return array
				 */
				'connections' => apply_filters( 'woographql_cart_connection_definitions', self::get_cart_connections() ),
			]
		);
	}

	/**
	 * Registers CartFee type
	 *
	 * @return void
	 */
	public static function register_cart_fee() {
		register_graphql_object_type(
			'CartFee',
			[
				'description' => __( 'An additional fee', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'id'       => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'Fee ID', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->id ) ? $source->id : null;
						},
					],
					'name'     => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => __( 'Fee name', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->name ) ? $source->name : null;
						},
					],
					'taxClass' => [
						'type'        => 'TaxClassEnum',
						'description' => __( 'Fee tax class', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->tax_class ) ? $source->tax_class : null;
						},
					],
					'taxable'  => [
						'type'        => 'Boolean',
						'description' => __( 'Is fee taxable?', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! is_null( $source->taxable ) ? $source->taxable : null;
						},
					],
					'amount'   => [
						'type'        => 'Float',
						'description' => __( 'Fee amount', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! is_null( $source->amount ) ? $source->amount : 0;
						},
					],
					'total'    => [
						'type'        => 'Float',
						'description' => __( 'Fee total', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! is_null( $source->total ) ? $source->total : 0;
						},
					],
				],
			]
		);
	}

	/**
	 * Registers CartTax type
	 *
	 * @return void
	 */
	public static function register_cart_tax() {
		register_graphql_object_type(
			'CartTax',
			[
				'description' => __( 'An itemized cart tax item', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'id'         => [
						'type'        => [ 'non_null' => 'ID' ],
						'description' => __( 'Tax Rate ID', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->tax_rate_id ) ? $source->tax_rate_id : null;
						},
					],
					'label'      => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => __( 'Tax label', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->label ) ? $source->label : null;
						},
					],
					'isCompound' => [
						'type'        => 'Boolean',
						'description' => __( 'Is tax compound?', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return ! empty( $source->is_compound ) ? $source->is_compound : null;
						},
					],
					'amount'     => [
						'type'        => 'String',
						'description' => __( 'Tax amount', 'wp-graphql-woocommerce' ),
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							],
						],
						'resolve'     => static function ( $source, array $args ) {
							$amount = ! empty( $source->amount ) ? $source->amount : null;

							if ( ! $amount ) {
								return null;
							}

							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $amount;
							}

							return wc_graphql_price( $amount );
						},
					],
				],
			]
		);
	}

	/**
	 * Registers AppliedCoupon type
	 *
	 * @return void
	 */
	public static function register_applied_coupon() {
		register_graphql_object_type(
			'AppliedCoupon',
			[
				'description' => __( 'Coupon applied to the shopping cart.', 'wp-graphql-woocommerce' ),
				'fields'      => [
					'code'           => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => __( 'Coupon code', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							return $source;
						},
					],
					'discountAmount' => [
						'type'        => [ 'non_null' => 'String' ],
						'args'        => [
							'excludeTax' => [
								'type'        => 'Boolean',
								'description' => __( 'Exclude Taxes (Default "true")', 'wp-graphql-woocommerce' ),
							],
							'format'     => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							],
						],
						'description' => __( 'Discount applied with this coupon', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args ) {
							$ex_tax = ! empty( $args['excludeTax'] ) ? $args['excludeTax'] : true;
							$amount = Factory::resolve_cart()->get_coupon_discount_amount( $source, $ex_tax );

							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $amount;
							}

							return wc_graphql_price( $amount );
						},
					],
					'discountTax'    => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => __( 'Taxes on discount applied with this coupon', 'wp-graphql-woocommerce' ),
						'args'        => [
							'format' => [
								'type'        => 'PricingFieldFormatEnum',
								'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
							],
						],
						'resolve'     => static function ( $source, array $args ) {
							$tax = Factory::resolve_cart()->get_coupon_discount_tax_amount( $source );

							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $tax;
							}

							return wc_graphql_price( $tax );
						},
					],
					'description'    => [
						'type'        => 'String',
						'description' => __( 'Description of applied coupon', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source, array $args ) {
							$coupon = new \WC_Coupon( $source );
							return $coupon->get_description();
						},
					],
				],
			]
		);
	}
}
