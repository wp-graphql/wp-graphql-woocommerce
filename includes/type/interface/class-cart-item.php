<?php
/**
 * WPInterface Type - CartItem
 *
 * @package WPGraphQL\WooCommerce\Type\WPInterface
 * @since   0.17.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInterface;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;
use WPGraphQL\WooCommerce\Data\Connection\Variation_Attribute_Connection_Resolver;

/**
 * Class Cart_Item
 */
class Cart_Item {
	/**
	 * Registers the "CartItem" interface and "SimpleCartItem" type..
	 *
	 * @return void
	 */
	public static function register_interface() {
		// Register cart item interface.
		register_graphql_interface_type(
			'CartItem',
			[
				'description' => __( 'Cart item interface.', 'wp-graphql-woocommerce' ),
				'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'connections' => self::get_connections(),
				'resolveType' => static function ( $cart_item ) {
					/**
					 * Instance of the WPGraphQL TypeRegistry.
					 *
					 * @var \WPGraphQL\Registry\TypeRegistry $type_registry
					 */
					$type_registry = \WPGraphQL::get_type_registry();

					$type_name = apply_filters( 'woographql_cart_item_type', 'SimpleCartItem', $cart_item );
					if ( empty( $type_name ) ) {
						throw new UserError( __( 'Invalid cart item type provided.', 'wp-graphql-woocommerce' ) );
					}

					return $type_registry->get_type( $type_name );
				},
			]
		);

		register_graphql_object_type(
			'SimpleCartItem',
			[
				'eagerlyLoadType' => true,
				'description'     => __( 'A item in the cart', 'wp-graphql-woocommerce' ),
				'interfaces'      => [ 'Node', 'CartItem' ],
				'fields'          => [],
			]
		);
	}

	/**
	 * Returns common cart field definitions.
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			'key'         => [
				'type'        => [ 'non_null' => 'ID' ],
				'description' => __( 'CartItem ID', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $source ) {
					return ! empty( $source['key'] ) ? $source['key'] : null;
				},
			],
			'quantity'    => [
				'type'        => 'Int',
				'description' => __( 'Quantity of the product', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $source ) {
					return isset( $source['quantity'] ) ? absint( $source['quantity'] ) : null;
				},
			],
			'subtotal'    => [
				'type'        => 'String',
				'description' => __( 'Item\'s subtotal', 'wp-graphql-woocommerce' ),
				'args'        => [
					'format' => [
						'type'        => 'PricingFieldFormatEnum',
						'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
					],
				],
				'resolve'     => static function ( $source, array $args ) {
					$price = isset( $source['line_subtotal'] ) ? floatval( $source['line_subtotal'] ) : 0;

					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						return $price;
					}

					return \wc_graphql_price( $price );
				},
			],
			'subtotalTax' => [
				'type'        => 'String',
				'description' => __( 'Item\'s subtotal tax', 'wp-graphql-woocommerce' ),
				'args'        => [
					'format' => [
						'type'        => 'PricingFieldFormatEnum',
						'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
					],
				],
				'resolve'     => static function ( $source, array $args ) {
					$price = isset( $source['line_subtotal_tax'] ) ? floatval( $source['line_subtotal_tax'] ) : 0;

					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						return $price;
					}

					return \wc_graphql_price( $price );
				},
			],
			'total'       => [
				'type'        => 'String',
				'description' => __( 'Item\'s total', 'wp-graphql-woocommerce' ),
				'args'        => [
					'format' => [
						'type'        => 'PricingFieldFormatEnum',
						'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
					],
				],
				'resolve'     => static function ( $source, array $args ) {
					$price_without_tax = isset( $source['line_total'] ) ? floatval( $source['line_total'] ) : 0;
					$tax               = isset( $source['line_tax'] ) ? floatval( $source['line_tax'] ) : 0;
					$price             = $price_without_tax + $tax;

					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						return $price;
					}

					return \wc_graphql_price( $price );
				},
			],
			'tax'         => [
				'type'        => 'String',
				'description' => __( 'Item\'s tax', 'wp-graphql-woocommerce' ),
				'args'        => [
					'format' => [
						'type'        => 'PricingFieldFormatEnum',
						'description' => __( 'Format of the price', 'wp-graphql-woocommerce' ),
					],
				],
				'resolve'     => static function ( $source, array $args ) {
					$price = isset( $source['line_tax'] ) ? floatval( $source['line_tax'] ) : 0;

					if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
						return $price;
					}

					return \wc_graphql_price( $price );
				},
			],
			'extraData'   => [
				'type'        => [ 'list_of' => 'MetaData' ],
				'description' => __( 'Object meta data', 'wp-graphql-woocommerce' ),
				'args'        => [
					'key'    => [
						'type'        => 'String',
						'description' => __( 'Retrieve meta by key', 'wp-graphql-woocommerce' ),
					],
					'keysIn' => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => __( 'Retrieve multiple metas by key', 'wp-graphql-woocommerce' ),
					],
				],
				'resolve'     => static function ( $source, array $args ) {
					// Check if "key" argument set and assigns to target "keys" array.
					if ( ! empty( $args['key'] ) && ! empty( $source[ $args['key'] ] ) ) {
						$keys = [ $args['key'] ];
					} elseif ( ! empty( $args['keysIn'] ) ) {
						// Check if "keysIn" argument set and assigns to target "keys" array.
						$keys = [];
						foreach ( $args['keysIn'] as $key ) {
							if ( ! empty( $source[ $key ] ) ) {
								$keys[] = $key;
							}
						}
					} else {
						// If no arguments set, all extra data keys are assigns to target "keys" array.
						$keys = array_diff(
							array_keys( $source ),
							[
								'key',
								'product_id',
								'variation_id',
								'variation',
								'quantity',
								'data',
								'data_hash',
								'line_tax_data',
								'line_subtotal',
								'line_subtotal_tax',
								'line_total',
								'line_tax',
							]
						);
					}//end if
					// Create meta ID prefix.
					$id_prefix = apply_filters( 'graphql_woocommerce_cart_meta_id_prefix', 'cart_' );

					// Format meta data for resolution.
					$data = [];
					foreach ( $keys as $key ) {
						$data[] = (object) [
							'id'    => "{$id_prefix}_{$key}",
							'key'   => $key,
							'value' => is_array( $source[ $key ] ) ? wp_json_encode( $source[ $key ] ) : $source[ $key ],
						];
					}

					return $data;
				},
			],
		];
	}

	/**
	 * Defines connections.
	 *
	 * @return array
	 */
	public static function get_connections() {
		return [
			'product'   => [
				'toType'     => 'Product',
				'oneToOne'   => true,
				'edgeFields' => [
					'simpleVariations' => [
						'type'        => [ 'list_of' => 'SimpleAttribute' ],
						'description' => __( 'Simple variation attribute data', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							$attributes = [];

							$variation             = $source['node'];
							$cart_item_data        = $source['source'];
							$simple_attribute_data = $cart_item_data['variation'];
							foreach ( $simple_attribute_data as $name => $value ) {
								$attributes[] = compact( 'name', 'value' );
							}

							return $attributes;
						},
					],
				],
				'resolve'    => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$id       = $source['product_id'];
					$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product' );

					return $resolver
						->one_to_one()
						->set_query_arg( 'p', $id )
						->get_connection();
				},
			],
			'variation' => [
				'toType'     => 'ProductVariation',
				'oneToOne'   => true,
				'edgeFields' => [
					'attributes' => [
						'type'        => [ 'list_of' => 'VariationAttribute' ],
						'description' => __( 'Attributes of the variation.', 'wp-graphql-woocommerce' ),
						'resolve'     => static function ( $source ) {
							$attributes = [];

							$variation           = $source['node'];
							$cart_item_data      = $source['source'];
							$cart_variation_data = $cart_item_data['variation'];
							foreach ( $variation->attributes as $name => $default_value ) {
								if ( isset( $cart_variation_data[ "attribute_{$name}" ] ) ) {
									$attributes[ $name ] = $cart_variation_data[ "attribute_{$name}" ];
								} else {
									$attributes[ $name ] = $default_value;
								}
							}

							return Variation_Attribute_Connection_Resolver::variation_attributes_to_data_array( $attributes, $variation->ID );
						},
					],
				],
				'resolve'    => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$id       = ! empty( $source['variation_id'] ) ? $source['variation_id'] : null;
					$resolver = new PostObjectConnectionResolver( $source, $args, $context, $info, 'product_variation' );

					if ( ! $id ) {
						return null;
					}

					return $resolver
						->one_to_one()
						->set_query_arg( 'p', $id )
						->get_connection();
				},
			],
		];
	}
}
