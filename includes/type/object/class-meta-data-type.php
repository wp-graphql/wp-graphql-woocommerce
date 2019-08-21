<?php
/**
 * WPObject Type - Order_Item_Type
 *
 * Registers MetaData type and queries
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPObject;

use WPGraphQL\AppContext;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;

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
					'key'   => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Meta key.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return ! empty( $source['key'] ) ? $source['key'] : null;
						},
					),
					'value' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Meta value.', 'wp-graphql-woocommerce' ),
						'resolve'     => function ( $source ) {
							return ! empty( $source['value'] ) ? $source['value'] : null;
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
					if ( ! empty( $args['key'] ) && ! empty( $source[ $args['key'] ] ) ) {
						$keys = array( $args['key'] );
					} elseif ( ! empty( $args['keysIn'] ) ) {
						$keys = array();
						foreach ( $args['keysIn'] as $key ) {
							if ( ! empty( $source[ $key ] ) ) {
								$keys[] = $key;
							}
						}
					} else {
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

					$data = array();
					foreach ( $keys as $key ) {
						$data[] = array(
							'key'   => $key,
							'value' => $source[ $key ],
						);
					}

					return $data;
				},
			)
		);

		$types = array(
			'Coupon',
			'Customer',
			'CouponLine',
			'LineItem',
			'FeeLine',
			'Order',
			'Product',
			'ProductVariation',
			'Refund',
			'ShippingLine',
			'TaxLine',
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
						'keysIn'     => array(
							'type'        => array( 'list_of' => 'String' ),
							'description' => __( 'Retrieve multiple metas by key', 'wp-graphql-woocommerce' ),
						),
						'multiple' => array(
							'type'        => 'Boolean',
							'description' => __( 'Retrieve meta with matching keys', 'wp-graphql-woocommerce' ),
						),
					),
					'resolve'     => function( $source, array $args ) {
						$single = ! empty( $args['multiple'] ) ? ! $args['multiple'] : true;
						$data   = array();

						if ( ! empty( $args['key'] ) ) {
							$data[ $args['key'] ] = $source->get_meta( $args['key'], $single );
						} elseif ( ! empty( $args['keysIn'] ) ) {
							$data = array();
							foreach ( $args['keysIn'] as $key ) {
								$data[ $key ] = $source->get_meta( $key, $single );
							}
						} else {
							$data = $source->get_meta_data();
						}

						\codecept_debug( $source->get_meta_data() );
						return ! empty( $data ) ? $data : null;
					},
				)
			);
		}
	}
}
