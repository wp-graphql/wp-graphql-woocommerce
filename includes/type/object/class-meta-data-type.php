<?php
/**
 * WPObject Type - Order_Item_Type
 *
 * Registers MetaData type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use WP_GraphQL_WooCommerce;

/**
 * Class Meta_Data_Type
 */
class Meta_Data_Type {

	/**
	 * Register Order type and queries to the WPGraphQL schema
	 */
	public static function register() {
		register_graphql_object_type(
			'MetaData',
			array(
				'description' => __( 'Extra data defined on the WC object', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'id'    => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Meta ID.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return ! empty( $source->id ) ? $source->id : null;
						},
					),
					'key'   => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Meta key.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return ! empty( $source->key ) ? $source->key : null;
						},
					),
					'value' => array(
						'type'        => 'String',
						'description' => __( 'Meta value.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return ! empty( $source->value ) ? $source->value : null;
						},
					),
				),
			)
		);

		// Register 'extraData' field on CartItem.
		register_graphql_field(
			'CartItem',
			'extraData',
			array(
				'type'        => array( 'list_of' => 'MetaData' ),
				'description' => __( 'Object meta data', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'key'    => array(
						'type'        => 'String',
						'description' => __( 'Retrieve meta by key', 'wp-graphql-woocommerce' ),
					),
					'keysIn' => array(
						'type'        => array( 'list_of' => 'String' ),
						'description' => __( 'Retrieve multiple metas by key', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function( $source, array $args ) {
					// Check if "key" argument set and assigns to target "keys" array.
					if ( ! empty( $args['key'] ) && ! empty( $source[ $args['key'] ] ) ) {
						$keys = array( $args['key'] );
					} elseif ( ! empty( $args['keysIn'] ) ) { // Check if "keysIn" argument set and assigns to target "keys" array.
						$keys = array();
						foreach ( $args['keysIn'] as $key ) {
							if ( ! empty( $source[ $key ] ) ) {
								$keys[] = $key;
							}
						}
					} else { // If no arguments set, all extra data keys are assigns to target "keys" array.
						$keys = array_diff(
							array_keys( $source ),
							array(
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
							)
						);
					}
					// Create meta ID prefix.
					$id_prefix = apply_filters( 'graphql_woocommerce_cart_meta_id_prefix', 'cart_' );

					// Format meta data for resolution.
					$data = array();
					foreach ( $keys as $key ) {
						$data[] = (object) array(
							'id'    => "{$id_prefix}_{$key}",
							'key'   => $key,
							'value' => $source[ $key ],
						);
					}

					return $data;
				},
			)
		);

		// Register 'metaData' field on WC CRUD types.
		$types = array_merge(
			array(
				'Coupon',
				'Customer',
				'CouponLine',
				'LineItem',
				'FeeLine',
				'Order',
				'ProductVariation',
				'Refund',
				'ShippingLine',
				'TaxLine',
			),
			array_values( WP_GraphQL_WooCommerce::get_enabled_product_types() )
		);

		foreach ( $types as $type ) {
			register_graphql_field(
				$type,
				'metaData',
				array(
					'type'        => array( 'list_of' => 'MetaData' ),
					'description' => __( 'Object meta data', 'wp-graphql-woocommerce' ),
					'args'        => array(
						'key'      => array(
							'type'        => 'String',
							'description' => __( 'Retrieve meta by key', 'wp-graphql-woocommerce' ),
						),
						'keysIn'   => array(
							'type'        => array( 'list_of' => 'String' ),
							'description' => __( 'Retrieve multiple metas by key', 'wp-graphql-woocommerce' ),
						),
						'multiple' => array(
							'type'        => 'Boolean',
							'description' => __( 'Retrieve meta with matching keys', 'wp-graphql-woocommerce' ),
						),
					),
					'resolve'     => function( $source, array $args ) {
						// Set unique flag.
						$single = ! empty( $args['multiple'] ) ? ! $args['multiple'] : true;

						// Check "key" argument and format meta_data objects.
						if ( ! empty( $args['key'] ) && $source->meta_exists( $args['key'] ) ) {
							$data = $source->get_meta( $args['key'], $single );
							if ( ! is_array( $data ) ) {
								$data = array_filter(
									$source->get_meta_data(),
									function( $meta ) use ( $data ) {
										return $meta->value === $data;
									}
								);
							}
						} elseif ( ! empty( $args['keysIn'] ) ) { // Check "keysIn" argument and format meta_data objects.
							$keys = $args['keysIn'];

							$found = array();
							$data = array_filter(
								$source->get_meta_data(),
								function( $meta ) use ( $keys, $single, &$found ) {
									if ( in_array( $meta->key, $keys, true ) ) {
										if ( $single ) {
											if ( ! in_array( $meta->key, $found, true ) ) {
												$found[] = $meta->key;
												return true;
											}
											return false;
										}
										return true;
									}
								}
							);
						} else { // If no arguments set return all meta (in accordance with unique flag).
							$found = array();
							$data = array_filter(
								$source->get_meta_data(),
								function( $meta ) use ( $single, &$found ) {
									if ( $single ) {
										if ( ! in_array( $meta->key, $found, true ) ) {
											$found[] = $meta->key;
											return true;
										}
										return false;
									}
									return true;
								}
							);
						}

						return ! empty( $data ) ? $data : null;
					},
				)
			);
		}
	}
}
